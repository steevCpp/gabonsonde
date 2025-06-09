<?php
// includes/process_order.php - Traitement des commandes de sondages personnalisés

session_start(); // Démarrer la session pour les messages flash

require_once '../includes/db_connect.php'; // Script de connexion à la base de données

// 1. Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur: Méthode de requête non autorisée.'];
    header("Location: ../public_html/commander.php");
    exit;
}

// 2. Récupérer et valider les données du formulaire
// Utiliser htmlspecialchars pour se prémunir contre XSS lors de l'affichage,
// mais pour la DB, les requêtes préparées sont la principale défense contre SQL injection.
$nom_complet = isset($_POST['nom_complet']) ? trim($_POST['nom_complet']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : null;
$organisation = isset($_POST['organisation']) ? trim($_POST['organisation']) : null;
$sujet_sondage = isset($_POST['sujet_sondage']) ? trim($_POST['sujet_sondage']) : '';
$public_cible = isset($_POST['public_cible']) ? trim($_POST['public_cible']) : null;
$nombre_questions = isset($_POST['nombre_questions']) && $_POST['nombre_questions'] !== '' ? filter_var(trim($_POST['nombre_questions']), FILTER_VALIDATE_INT) : null;
$objectifs = isset($_POST['objectifs']) ? trim($_POST['objectifs']) : '';
$budget = isset($_POST['budget']) ? trim($_POST['budget']) : null;
$autres_informations = isset($_POST['autres_informations']) ? trim($_POST['autres_informations']) : null;

// Validation des champs obligatoires
if (empty($nom_complet) || empty($email) || empty($sujet_sondage) || empty($objectifs)) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur: Veuillez remplir tous les champs obligatoires (Nom, Email, Sujet, Objectifs).'];
    // Pour pré-remplir le formulaire, il faudrait stocker les valeurs POSTées en session aussi.
    // Pour cette tâche, un message d'erreur simple suffit.
    header("Location: commander.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur: Format d\'email invalide.'];
    header("Location: commander.php");
    exit;
}

if ($nombre_questions !== null && $nombre_questions === false) { // Si une valeur a été fournie mais n'est pas un entier valide
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur: Le nombre approximatif de questions doit être un nombre entier.'];
    header("Location: commander.php");
    exit;
}


// 3. Établir une connexion à la base de données
$dbconn = connectDB();
if (!$dbconn) {
    error_log("process_order.php: Failed to connect to DB.");
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur technique: Impossible d\'envoyer votre demande pour le moment.'];
    header("Location: commander.php"); // Rediriger vers commander.php en cas d'erreur DB
    exit;
}

// 4. Préparer et exécuter la requête d'insertion
$sql = "INSERT INTO survey_orders (
            full_name, email, phone, organization,
            survey_subject, target_audience, approx_questions,
            objectives, budget, additional_info,
            status, submitted_at
        ) VALUES (
            $1, $2, $3, $4, $5, $6, $7, $8, $9, $10,
            'pending', NOW()
        )";

$stmt_name = "insert_survey_order";
if (!pg_prepare($dbconn, $stmt_name, $sql)) {
    $db_error = pg_last_error($dbconn);
    error_log("process_order.php: Failed to prepare statement. Error: " . $db_error);
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur technique: Impossible de traiter votre demande (préparation).'];
    header("Location: commander.php");
    exit;
}

$params = array(
    $nom_complet, $email, $telephone, $organisation,
    $sujet_sondage, $public_cible, $nombre_questions,
    $objectifs, $budget, $autres_informations
);

$result = pg_execute($dbconn, $stmt_name, $params);

// 5. Gestion du résultat de l'insertion
if ($result) {
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Votre demande de sondage personnalisé a bien été envoyée. Nous vous contacterons prochainement.'];
} else {
    $db_error = pg_last_error($dbconn);
    error_log("process_order.php: Failed to insert survey order. Error: " . $db_error);
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Une erreur est survenue lors de l\'envoi de votre demande. Veuillez réessayer. Erreur: ' . $db_error ];
}

// 6. Fermer la connexion à la base de données
if ($dbconn) {
    pg_close($dbconn);
}

// 7. Rediriger l'utilisateur vers merci.php
header("Location: merci.php");
exit;

?>
