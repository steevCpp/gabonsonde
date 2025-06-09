<?php
// resultats.php - Affichage des résultats des sondages

session_start(); // Pour afficher les messages flash (si process_survey redirige ici avec un message)

require_once '../includes/db_connect.php'; // Ajuster le chemin

$dbconn = connectDB();

// Gérer l'échec de connexion globalement au début peut être plus propre
if (!$dbconn) {
    $pageTitle = "Erreur de base de données";
    $error_message = "Impossible de se connecter à la base de données pour afficher les résultats. Veuillez réessayer plus tard.";
    // Pour éviter d'appeler header/footer sans $dbconn valide si header/footer font des appels DB (improbable mais possible)
    // Temporairement, on fait un die() simple. Idéalement, une page d'erreur statique ou un mécanisme plus robuste.
    // die($error_message);
    // Pour une meilleure intégration :
    include '../includes/header.php'; // Ce chemin est déjà correct
    echo '<div class="container mt-4"><div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div></div>';
    include '../includes/footer.php'; // Ce chemin est déjà correct
    exit;
}

include '../includes/header.php'; // Ce chemin est déjà correct
?>

<div class="container mt-4 results-specific-container">

    <?php
    // Afficher les messages de session s'il y en a
    if (isset($_SESSION['message'])) {
        $msg_type = $_SESSION['message']['type'] === 'success' ? 'alert-success' : 'alert-danger';
        echo '<div class="alert ' . $msg_type . '" role="alert">' . htmlspecialchars($_SESSION['message']['text']) . '</div>';
        unset($_SESSION['message']); // Supprimer le message après l'affichage
    }

    // Vérifier si un survey_id est passé dans l'URL
    if (isset($_GET['survey_id']) && filter_var($_GET['survey_id'], FILTER_VALIDATE_INT, array("options" => array("min_range"=>1)))) {
        $survey_id = (int)$_GET['survey_id'];

        // --- Afficher les résultats d'un sondage spécifique ---
        $sql_survey_details = "SELECT title, description FROM surveys WHERE id = $1";
        if (!pg_prepare($dbconn, "get_survey_res_details", $sql_survey_details)) {
            echo '<div class="alert alert-danger">Erreur préparation détails sondage.</div>';
        } else {
            $result_survey_details = pg_execute($dbconn, "get_survey_res_details", array($survey_id));

            if ($result_survey_details && pg_num_rows($result_survey_details) > 0) {
                $survey = pg_fetch_assoc($result_survey_details);
                echo '<h1>Résultats du Sondage: ' . htmlspecialchars($survey['title']) . '</h1>';
                if (!empty($survey['description'])) {
                    echo '<p class="lead">' . nl2br(htmlspecialchars($survey['description'])) . '</p>';
                }
                echo '<hr>';

                // Récupérer les questions
                $sql_questions = "SELECT id AS question_id, question_text, question_type FROM questions WHERE survey_id = $1 ORDER BY question_order ASC";
                if (!pg_prepare($dbconn, "get_res_questions", $sql_questions)) {
                     echo '<div class="alert alert-danger">Erreur préparation questions.</div>';
                } else {
                    $result_questions = pg_execute($dbconn, "get_res_questions", array($survey_id));

                    if (pg_num_rows($result_questions) > 0) {
                        while ($question = pg_fetch_assoc($result_questions)) {
                            $question_id = $question['question_id'];
                            $question_text = htmlspecialchars($question['question_text']);
                            $question_type = $question['question_type'];

                            echo '<div class="survey-result card shadow-sm mb-4">'; // Utilisation des classes de resultats.php
                            echo '<div class="card-body">';
                            echo '<h3 class="card-title h5">' . $question_text . '</h3>';

                            // Nombre total de réponses pour cette question
                            $sql_total_q_responses = "SELECT COUNT(*) as total_count FROM responses WHERE survey_id = $1 AND question_id = $2";
                            pg_prepare($dbconn, "total_q_resp_q".$question_id, $sql_total_q_responses);
                            $res_total_q = pg_execute($dbconn, "total_q_resp_q".$question_id, array($survey_id, $question_id));
                            $total_q_responses = ($res_total_q && pg_num_rows($res_total_q) > 0) ? (int)pg_fetch_result($res_total_q, 0, 'total_count') : 0;

                            echo '<p class="text-muted"><small>Nombre total de répondants pour cette question: ' . $total_q_responses . '</small></p>';

                            if ($total_q_responses > 0) {
                                switch ($question_type) {
                                    case 'yes_no':
                                        pg_prepare($dbconn, "yes_no_count_q".$question_id, "SELECT lower(response_text) as answer_val, COUNT(*) as count FROM responses WHERE survey_id = $1 AND question_id = $2 AND lower(response_text) IN ('yes', 'no') GROUP BY lower(response_text)");
                                        $res_yes_no = pg_execute($dbconn, "yes_no_count_q".$question_id, array($survey_id, $question_id));
                                        $counts = ['yes' => 0, 'no' => 0];
                                        while($row = pg_fetch_assoc($res_yes_no)) { $counts[$row['answer_val']] = (int)$row['count']; }

                                        foreach (['yes' => 'Oui', 'no' => 'Non'] as $val => $label) {
                                            $percentage = ($total_q_responses > 0) ? round(($counts[$val] / $total_q_responses) * 100, 1) : 0;
                                            echo '<div><p>' . $label . ': ' . $percentage . '% (' . $counts[$val] . ' votes)</p>';
                                            echo '<div class="progress mb-2" style="height: 20px;"><div class="progress-bar" role="progressbar" style="width: ' . $percentage . '%;" aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100">' . $percentage . '%</div></div></div>';
                                        }
                                        break;

                                    case 'multiple_choice_single':
                                    case 'rating_scale':
                                        $sql_options = "SELECT id AS option_id, option_text FROM options WHERE question_id = $1 ORDER BY option_order ASC";
                                        pg_prepare($dbconn, "get_opts_q".$question_id, $sql_options);
                                        $res_options = pg_execute($dbconn, "get_opts_q".$question_id, array($question_id));

                                        while ($option = pg_fetch_assoc($res_options)) {
                                            $option_id = $option['option_id'];
                                            $option_text = htmlspecialchars($option['option_text']);

                                            pg_prepare($dbconn, "count_opt_q".$question_id."_opt".$option_id, "SELECT COUNT(*) as count FROM responses WHERE survey_id = $1 AND question_id = $2 AND option_id = $3");
                                            $res_opt_count = pg_execute($dbconn, "count_opt_q".$question_id."_opt".$option_id, array($survey_id, $question_id, $option_id));
                                            $opt_count = ($res_opt_count && pg_num_rows($res_opt_count) > 0) ? (int)pg_fetch_result($res_opt_count, 0, 'count') : 0;

                                            $percentage = ($total_q_responses > 0) ? round(($opt_count / $total_q_responses) * 100, 1) : 0;
                                            echo '<div><p>' . $option_text . ': ' . $percentage . '% (' . $opt_count . ' votes)</p>';
                                            echo '<div class="progress mb-2" style="height: 20px;"><div class="progress-bar" role="progressbar" style="width: ' . $percentage . '%;" aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100">' . $percentage . '%</div></div></div>';
                                        }
                                        break;

                                    case 'multiple_choice_multiple':
                                        $sql_options_mcm = "SELECT id AS option_id, option_text FROM options WHERE question_id = $1 ORDER BY option_order ASC";
                                        pg_prepare($dbconn, "get_opts_mcm_q".$question_id, $sql_options_mcm);
                                        $res_options_mcm = pg_execute($dbconn, "get_opts_mcm_q".$question_id, array($question_id));

                                        while ($option = pg_fetch_assoc($res_options_mcm)) {
                                            $option_id = $option['option_id'];
                                            $option_text = htmlspecialchars($option['option_text']);

                                            pg_prepare($dbconn, "count_mcm_opt_q".$question_id."_opt".$option_id, "SELECT COUNT(*) as count FROM responses WHERE survey_id = $1 AND question_id = $2 AND option_id = $3");
                                            $res_mcm_opt_count = pg_execute($dbconn, "count_mcm_opt_q".$question_id."_opt".$option_id, array($survey_id, $question_id, $option_id));
                                            $mcm_opt_count = ($res_mcm_opt_count && pg_num_rows($res_mcm_opt_count) > 0) ? (int)pg_fetch_result($res_mcm_opt_count, 0, 'count') : 0;

                                            // For multiple choice multiple, percentage is based on total respondents to THIS question, not total options selected.
                                            $percentage = ($total_q_responses > 0) ? round(($mcm_opt_count / $total_q_responses) * 100, 1) : 0;
                                            echo '<div><p>' . $option_text . ': ' . $percentage . '% (' . $mcm_opt_count . ' fois choisi)</p>';
                                            echo '<div class="progress mb-2" style="height: 20px;"><div class="progress-bar" role="progressbar" style="width: ' . $percentage . '%;" aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100">' . $percentage . '%</div></div></div>';
                                        }
                                        break;

                                    case 'open_text':
                                        echo '<p class="text-muted"><em>Les réponses textuelles sont analysées séparément et ne sont pas affichées ici.</em></p>';
                                        // Optionally, list some recent responses (ensure htmlspecialchars)
                                        // $sql_text_responses = "SELECT response_text FROM responses WHERE survey_id = $1 AND question_id = $2 AND response_text IS NOT NULL ORDER BY submitted_at DESC LIMIT 5";
                                        // ... execute and display ...
                                        break;
                                }
                            } else {
                                echo '<p><em>Aucune réponse enregistrée pour cette question pour le moment.</em></p>';
                            }
                            echo '</div></div>'; // Close card-body and survey-result card
                        }
                    } else {
                         echo '<p>Ce sondage ne contient pas encore de questions.</p>';
                    }
                }
                 echo '<div class="mt-4"><a href="resultats.php" class="btn btn-secondary">Retour à la liste des sondages avec résultats</a></div>';
            } else {
                echo '<h1>Sondage non trouvé</h1>';
                echo '<div class="alert alert-warning">Le sondage demandé est introuvable ou n\'a pas encore de résultats.</div>';
                echo '<a href="resultats.php" class="btn btn-secondary">Retour à la liste des sondages</a>';
            }
        }

    } else {
        // --- Afficher la liste des sondages avec résultats ---
        echo '<h1>Résultats des Sondages</h1>';
        echo '<p class="lead">Sélectionnez un sondage pour visualiser ses résultats.</p><hr>';

        $sql_list_surveys_with_results = "SELECT DISTINCT s.id, s.title, s.description FROM surveys s JOIN responses r ON s.id = r.survey_id ORDER BY s.title ASC";
        $result_list_surveys = pg_query($dbconn, $sql_list_surveys_with_results); // No user input, direct query is fine

        if ($result_list_surveys && pg_num_rows($result_list_surveys) > 0) {
            echo '<div class="list-group">';
            while ($survey_item = pg_fetch_assoc($result_list_surveys)) {
                echo '<a href="resultats.php?survey_id=' . htmlspecialchars($survey_item['id']) . '" class="list-group-item list-group-item-action flex-column align-items-start mb-3 border rounded shadow-sm">';
                echo '<div class="d-flex w-100 justify-content-between">';
                echo '<h5 class="mb-1">' . htmlspecialchars($survey_item['title']) . '</h5>';
                echo '</div>';
                if (!empty($survey_item['description'])) {
                    echo '<p class="mb-1 text-muted"><small>' . nl2br(htmlspecialchars($survey_item['description'])) . '</small></p>';
                }
                echo '<small class="text-primary">Voir les résultats &raquo;</small>';
                echo '</a>';
            }
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">Aucun résultat de sondage disponible pour le moment.</div>';
        }
    }
    ?>
</div> <!-- /container results-specific-container -->

<?php
// Fermer la connexion à la base de données
if ($dbconn) {
    pg_close($dbconn);
}

include '../includes/footer.php'; // Ce chemin est déjà correct
?>
