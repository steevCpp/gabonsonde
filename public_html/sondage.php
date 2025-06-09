<?php
// sondage.php - Page d'affichage des sondages actifs ou d'un sondage spécifique.

// Inclure le script de connexion à la base de données
require_once '../includes/db_connect.php'; // Ce chemin est déjà correct

// Établir la connexion à la base de données
$dbconn = connectDB();

// Si la connexion échoue, afficher un message d'erreur et arrêter le script.
if (!$dbconn) {
    // Le message d'erreur détaillé est déjà loggué par connectDB()
    // Afficher un message convivial à l'utilisateur.
    // Il serait préférable d'inclure header.php et footer.php ici pour une page d'erreur cohérente.
    // Pour la simplicité de cette étape, on affiche un message direct.
    // die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
    // Pour une meilleure intégration :
    $pageTitle = "Erreur";
    $error_message = "Nous rencontrons actuellement des difficultés techniques pour accéder aux sondages. Veuillez réessayer plus tard.";
    include '../includes/header.php'; // Ce chemin est déjà correct
    echo '<div class="container mt-4"><div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div></div>';
    include '../includes/footer.php'; // Ce chemin est déjà correct
    exit; // Arrêter l'exécution du script
}

// Inclure le header HTML
// Le titre de la page sera défini plus bas en fonction du contexte.
// $pageTitle variable can be set here if needed by header.php
include '../includes/header.php'; // Ce chemin est déjà correct

?>

<div class="container mt-4 survey-specific-container"> <!-- survey-specific-container pour le style de fond blanc/ombre -->

<?php
// Vérifier si un survey_id est passé dans l'URL
if (isset($_GET['survey_id']) && filter_var($_GET['survey_id'], FILTER_VALIDATE_INT, array("options" => array("min_range"=>1)))) {
    $survey_id = (int)$_GET['survey_id'];

    // --- Afficher un sondage spécifique ---

    // Préparer la requête pour récupérer les détails du sondage
    $sql_survey = "SELECT title, description FROM surveys WHERE id = $1 AND status = 'active'";
    if (!pg_prepare($dbconn, "get_survey_details", $sql_survey)) {
        // Gérer l'échec de la préparation
        echo '<div class="alert alert-danger">Erreur lors de la préparation de la requête du sondage.</div>';
    } else {
        $result_survey = pg_execute($dbconn, "get_survey_details", array($survey_id));

        if ($result_survey && pg_num_rows($result_survey) > 0) {
            $survey = pg_fetch_assoc($result_survey);
            echo '<h1>' . htmlspecialchars($survey['title']) . '</h1>';
            if (!empty($survey['description'])) {
                echo '<p class="lead">' . nl2br(htmlspecialchars($survey['description'])) . '</p>';
            }
            echo '<hr>';

            // Récupérer les questions pour ce sondage
            $sql_questions = "SELECT id, question_text, question_type, question_order FROM questions WHERE survey_id = $1 ORDER BY question_order ASC";
            if (!pg_prepare($dbconn, "get_survey_questions", $sql_questions)) {
                 echo '<div class="alert alert-danger">Erreur lors de la préparation de la requête des questions.</div>';
            } else {
                $result_questions = pg_execute($dbconn, "get_survey_questions", array($survey_id));

                if ($result_questions && pg_num_rows($result_questions) > 0) {
                    echo '<form class="survey-form mt-4" action="process_survey.php" method="post">';
                    echo '<input type="hidden" name="survey_id" value="' . htmlspecialchars($survey_id) . '">';
                    echo '<input type="hidden" name="user_id" value="1">'; // Temporaire: Assigner à l'utilisateur 1 ou gérer l'authentification/anonymat

                    $question_counter = 0;
                    while ($question = pg_fetch_assoc($result_questions)) {
                        $question_counter++;
                        $question_id = $question['id'];
                        $question_text = htmlspecialchars($question['question_text']);
                        $question_type = $question['question_type'];

                        echo '<div class="mb-4 p-3 border rounded question-block">'; // Bootstrap margin and padding
                        echo '<p class="fw-bold">' . $question_counter . '. ' . $question_text . '</p>';

                        // Générer les champs de formulaire en fonction du type de question
                        switch ($question_type) {
                            case 'yes_no':
                                echo '<div class="form-check"><input class="form-check-input" type="radio" name="question[' . $question_id . ']" id="q' . $question_id . '_yes" value="yes" required><label class="form-check-label" for="q' . $question_id . '_yes">Oui</label></div>';
                                echo '<div class="form-check"><input class="form-check-input" type="radio" name="question[' . $question_id . ']" id="q' . $question_id . '_no" value="no" required><label class="form-check-label" for="q' . $question_id . '_no">Non</label></div>';
                                break;

                            case 'multiple_choice_single':
                            case 'rating_scale':
                                $sql_options = "SELECT id, option_text, option_value FROM options WHERE question_id = $1 ORDER BY option_order ASC";
                                if (!pg_prepare($dbconn, "get_question_options_q" . $question_id, $sql_options)) { // Unique statement name per question
                                    echo '<div class="alert alert-warning">Erreur préparation options.</div>';
                                } else {
                                    $result_options = pg_execute($dbconn, "get_question_options_q" . $question_id, array($question_id));
                                    if ($result_options && pg_num_rows($result_options) > 0) {
                                        while ($option = pg_fetch_assoc($result_options)) {
                                            $option_id_val = $option['id'];
                                            $option_text_val = htmlspecialchars($option['option_text']);
                                            // Utiliser option_value si disponible et non vide, sinon option_text
                                            $radio_value = (!empty($option['option_value'])) ? htmlspecialchars($option['option_value']) : $option_text_val;
                                            echo '<div class="form-check">';
                                            echo '<input class="form-check-input" type="radio" name="question[' . $question_id . ']" id="opt' . $option_id_val . '" value="' . $option_id_val . '" required>'; // Stocker option_id
                                            echo '<label class="form-check-label" for="opt' . $option_id_val . '">' . $option_text_val . '</label>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<p>Aucune option disponible pour cette question.</p>';
                                    }
                                }
                                break;

                            case 'multiple_choice_multiple':
                                $sql_options_mc = "SELECT id, option_text, option_value FROM options WHERE question_id = $1 ORDER BY option_order ASC";
                                 if (!pg_prepare($dbconn, "get_question_options_mc_q" . $question_id, $sql_options_mc)) {
                                    echo '<div class="alert alert-warning">Erreur préparation options.</div>';
                                } else {
                                    $result_options_mc = pg_execute($dbconn, "get_question_options_mc_q" . $question_id, array($question_id));
                                    if ($result_options_mc && pg_num_rows($result_options_mc) > 0) {
                                        while ($option = pg_fetch_assoc($result_options_mc)) {
                                            $option_id_val = $option['id'];
                                            $option_text_val = htmlspecialchars($option['option_text']);
                                            echo '<div class="form-check">';
                                            echo '<input class="form-check-input" type="checkbox" name="question[' . $question_id . '][]" id="opt' . $option_id_val . '" value="' . $option_id_val . '">'; // Stocker option_id
                                            echo '<label class="form-check-label" for="opt' . $option_id_val . '">' . $option_text_val . '</label>';
                                            echo '</div>';
                                        }
                                    } else {
                                         echo '<p>Aucune option disponible pour cette question.</p>';
                                    }
                                }
                                break;

                            case 'open_text':
                                echo '<div class="mb-3"><textarea class="form-control" name="question[' . $question_id . ']" rows="3" required></textarea></div>';
                                break;
                        }
                        echo '</div>'; // Close question-block
                    }
                    echo '<hr>';
                    echo '<button type="submit" class="btn btn-primary btn-lg mt-3">Soumettre mes réponses</button>';
                    echo '</form>';
                } else {
                    echo '<p>Ce sondage ne contient actuellement aucune question.</p>';
                }
            }
        } else {
            echo '<h1>Sondage non trouvé</h1>';
            echo '<div class="alert alert-warning">Le sondage demandé est introuvable ou n\'est pas actuellement actif.</div>';
            echo '<a href="sondage.php" class="btn btn-secondary">Retour à la liste des sondages</a>';
        }
    }

} else {
    // --- Afficher la liste des sondages actifs ---
    echo '<h1>Participer à un sondage</h1>';
    echo '<p class="lead">Choisissez un sondage parmi la liste ci-dessous pour donner votre avis.</p><hr>';

    $sql_list_surveys = "SELECT id, title, description FROM surveys WHERE status = 'active' ORDER BY created_at DESC";
    // Pas besoin de pg_prepare pour une requête sans paramètres utilisateur directs, mais bonne pratique si on ajoutait des filtres.
    $result_list_surveys = pg_query($dbconn, $sql_list_surveys);

    if ($result_list_surveys && pg_num_rows($result_list_surveys) > 0) {
        echo '<div class="list-group">';
        while ($survey_item = pg_fetch_assoc($result_list_surveys)) {
            echo '<a href="sondage.php?survey_id=' . htmlspecialchars($survey_item['id']) . '" class="list-group-item list-group-item-action flex-column align-items-start mb-3 border rounded shadow-sm">';
            echo '<div class="d-flex w-100 justify-content-between">';
            echo '<h5 class="mb-1">' . htmlspecialchars($survey_item['title']) . '</h5>';
            echo '</div>';
            if (!empty($survey_item['description'])) {
                echo '<p class="mb-1">' . nl2br(htmlspecialchars($survey_item['description'])) . '</p>';
            }
            echo '<small class="text-muted">Cliquez pour participer</small>';
            echo '</a>';
        }
        echo '</div>';
    } else {
        echo '<div class="alert alert-info">Aucun sondage actif pour le moment. Veuillez revenir plus tard !</div>';
    }
}

?>
</div> <!-- /container survey-specific-container -->

<?php
// Fermer la connexion à la base de données
if ($dbconn) {
    pg_close($dbconn);
}

// Inclure le footer HTML
include '../includes/footer.php'; // Ce chemin est déjà correct
?>
