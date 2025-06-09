<?php
// includes/process_survey.php - Traitement des soumissions de sondages

session_start(); // Démarrer la session pour les messages flash

require_once '../includes/db_connect.php'; // Connexion à la base de données

// 1. Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si la méthode n'est pas POST, rediriger ou afficher une erreur
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur: Méthode de requête non autorisée.'];
    header("Location: sondage.php");
    exit;
}

// 2. Récupérer et valider les données soumises
$survey_id = filter_input(INPUT_POST, 'survey_id', FILTER_VALIDATE_INT);
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT); // Peut être NULL/0 si anonyme et géré comme tel
$questions_responses = isset($_POST['question']) ? $_POST['question'] : null;

if (!$survey_id || $questions_responses === null) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur: Données du sondage manquantes ou invalides.'];
    // Tenter de rediriger vers le sondage spécifique si survey_id est connu, sinon page générale
    $redirect_url = $survey_id ? "sondage.php?survey_id=" . $survey_id : "sondage.php";
    header("Location: " . $redirect_url);
    exit;
}

// Si user_id n'est pas fourni ou est 0, le traiter comme NULL (pour les réponses anonymes)
if (empty($user_id)) {
    $user_id = null;
}

// 3. Établir une connexion à la base de données
$dbconn = connectDB();
if (!$dbconn) {
    error_log("process_survey.php: Failed to connect to DB.");
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur technique: Impossible de soumettre vos réponses pour le moment.'];
    header("Location: sondage.php?survey_id=" . $survey_id);
    exit;
}

// 4. Traiter chaque réponse dans une transaction
$all_insertions_successful = true;
pg_query($dbconn, "BEGIN"); // Démarrer la transaction

foreach ($questions_responses as $question_id => $answer_value) {
    if (!filter_var($question_id, FILTER_VALIDATE_INT)) {
        $all_insertions_successful = false;
        error_log("process_survey.php: Invalid question_id received: " . $question_id);
        break;
    }
    $question_id = (int)$question_id;

    // Récupérer le type de question pour savoir comment stocker la réponse
    $sql_question_type = "SELECT question_type FROM questions WHERE id = $1";
    if (!pg_prepare($dbconn, "get_q_type_q" . $question_id, $sql_question_type)) { // Unique name for prepared statement
        error_log("process_survey.php: Failed to prepare statement get_q_type_q" . $question_id . " Error: " . pg_last_error($dbconn));
        $all_insertions_successful = false;
        break;
    }
    $result_q_type = pg_execute($dbconn, "get_q_type_q" . $question_id, array($question_id));

    if (!$result_q_type || pg_num_rows($result_q_type) == 0) {
        error_log("process_survey.php: Could not find question type for question_id: " . $question_id);
        $all_insertions_successful = false;
        break;
    }
    $question_data = pg_fetch_assoc($result_q_type);
    $question_type = $question_data['question_type'];

    // Préparer la requête d'insertion dans la table `responses`
    $sql_insert_response = "INSERT INTO responses (survey_id, question_id, user_id, option_id, response_text, submitted_at) VALUES ($1, $2, $3, $4, $5, NOW())";
    // Unique name for prepared statement for insertion, using question_id to ensure uniqueness if loop runs fast
    $stmt_name = "insert_response_q" . $question_id;
    if (!pg_prepare($dbconn, $stmt_name, $sql_insert_response)) {
        error_log("process_survey.php: Failed to prepare insert statement " . $stmt_name . " Error: " . pg_last_error($dbconn));
        $all_insertions_successful = false;
        break;
    }

    // Adapter l'insertion en fonction du type de question
    if ($question_type == 'multiple_choice_multiple') {
        if (is_array($answer_value)) {
            foreach ($answer_value as $single_option_id) {
                $single_option_id = filter_var($single_option_id, FILTER_VALIDATE_INT);
                if ($single_option_id === false) {
                     error_log("process_survey.php: Invalid option_id in multiple choice for question_id: " . $question_id);
                     $all_insertions_successful = false; // Or continue and skip this option
                     continue; // Skip this invalid option
                }
                $result_exec = pg_execute($dbconn, $stmt_name, array($survey_id, $question_id, $user_id, $single_option_id, null));
                if (!$result_exec) {
                    error_log("process_survey.php: Failed to insert multiple choice option. Q_ID: " . $question_id . ", Opt_ID: " . $single_option_id . ". Error: " . pg_last_error($dbconn));
                    $all_insertions_successful = false;
                    break; // Exit inner loop
                }
            }
            if (!$all_insertions_successful) break; // Exit outer loop if an error occurred
        } else {
            // Expected an array for multiple choice, but didn't get one. Could be an error or no options selected.
            // If no options selected is valid, this is fine. If at least one is required, validation should be client-side or pre-loop.
            // For now, assume it's okay if no option is selected for a checkbox group.
        }
    } elseif ($question_type == 'open_text') {
        $response_text = is_string($answer_value) ? trim($answer_value) : null;
        if (empty($response_text) && !is_null($response_text)) { // Allow empty string if that's the input, but treat truly empty (e.g. from just spaces) as NULL or handle as error if required
             // For this example, let's allow empty strings if that's what trim results in.
        }
        $result_exec = pg_execute($dbconn, $stmt_name, array($survey_id, $question_id, $user_id, null, $response_text));
        if (!$result_exec) {
            error_log("process_survey.php: Failed to insert open text response. Q_ID: " . $question_id . ". Error: " . pg_last_error($dbconn));
            $all_insertions_successful = false;
            break;
        }
    } elseif ($question_type == 'yes_no') {
        // Storing 'yes'/'no' string directly in response_text as decided.
        $response_text_yn = ($answer_value == 'yes' || $answer_value == 'no') ? $answer_value : null;
        if ($response_text_yn === null) {
            error_log("process_survey.php: Invalid value for yes/no question_id: " . $question_id . ". Value: " . $answer_value);
            $all_insertions_successful = false;
            break;
        }
        $result_exec = pg_execute($dbconn, $stmt_name, array($survey_id, $question_id, $user_id, null, $response_text_yn));
        if (!$result_exec) {
            error_log("process_survey.php: Failed to insert yes/no response. Q_ID: " . $question_id . ". Error: " . pg_last_error($dbconn));
            $all_insertions_successful = false;
            break;
        }
    } else { // 'multiple_choice_single', 'rating_scale'
        $option_id = filter_var($answer_value, FILTER_VALIDATE_INT);
        if ($option_id === false) {
            error_log("process_survey.php: Invalid option_id for single choice/rating question_id: " . $question_id . ". Value: " . $answer_value);
            $all_insertions_successful = false;
            break;
        }
        $result_exec = pg_execute($dbconn, $stmt_name, array($survey_id, $question_id, $user_id, $option_id, null));
        if (!$result_exec) {
            error_log("process_survey.php: Failed to insert single choice/rating response. Q_ID: " . $question_id . ". Opt_ID: " . $option_id . ". Error: " . pg_last_error($dbconn));
            $all_insertions_successful = false;
            break;
        }
    }
}

// 5. Valider ou annuler la transaction
if ($all_insertions_successful) {
    pg_query($dbconn, "COMMIT");
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Merci d\'avoir participé au sondage ! Vos réponses ont été enregistrées.'];
} else {
    pg_query($dbconn, "ROLLBACK");
    // Check if a specific message was already set, otherwise set a generic one
    if (!isset($_SESSION['message'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Une erreur est survenue lors de l\'enregistrement de vos réponses. Veuillez réessayer.'];
    }
}

// 6. Fermer la connexion à la base de données
if ($dbconn) {
    pg_close($dbconn);
}

// 7. Rediriger l'utilisateur
// Idéalement vers une page de remerciement dédiée. Pour l'instant, retour à sondage.php.
// La page de destination (sondage.php) devra être capable d'afficher les messages de session.
header("Location: sondage.php"); // Peut-être ajouter ?survey_id=$survey_id si on veut retourner au sondage (mais il est complété)
exit;

?>
