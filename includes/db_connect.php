<?php
// Script de connexion à la base de données PostgreSQL pour GabonSonde

// Inclure les informations de configuration de la base de données
require_once 'db_config.php';

/**
 * Établit une connexion à la base de données PostgreSQL.
 *
 * Utilise les constantes définies dans db_config.php pour se connecter.
 *
 * @return resource|false L'identifiant de connexion PostgreSQL en cas de succès,
 *                        ou false si la connexion échoue.
 */
function connectDB() {
    // Construction de la chaîne de connexion pour pg_connect()
    // Exemple: "host=localhost port=5432 dbname=mydatabase user=myuser password=mypassword"
    $conn_string = "host=" . DB_HOST .
                   " port=" . DB_PORT .
                   " dbname=" . DB_NAME .
                   " user=" . DB_USER .
                   " password=" . DB_PASSWORD;

    // Tenter la connexion à la base de données
    // L'utilisation de '@' supprime les avertissements PHP natifs en cas d'échec,
    // car nous allons gérer l'erreur nous-mêmes.
    $dbconn = @pg_connect($conn_string);

    // Vérifier si la connexion a échoué
    if (!$dbconn) {
        // En environnement de développement, afficher l'erreur peut être utile.
        // En production, il est préférable de logguer l'erreur et d'afficher un message générique à l'utilisateur.

        // Log l'erreur de connexion (recommandé pour la production)
        // Le message d'erreur exact de pg_last_error() peut révéler des informations sensibles,
        // donc il est préférable de logguer un message plus générique ou contrôlé.
        error_log("PostgreSQL Connection Error: Failed to connect to database '" . DB_NAME . "' on host '" . DB_HOST . "'.");

        // Pour le développement, on peut temporairement afficher plus de détails:
        // $error_message = pg_last_error(); // Attention si $dbconn est false, pg_last_error() sur $dbconn n'est pas possible.
                                         // pg_last_error() sans argument récupère la dernière erreur du module PostgreSQL.
        // die("Erreur de connexion à la base de données PostgreSQL. Message: " . $error_message);

        // Retourner false pour permettre à l'application appelante de gérer l'échec
        return false;
    }

    // La connexion est réussie
    return $dbconn;
}

// --- Section de test de la connexion (Optionnel) ---
// Décommentez les lignes ci-dessous pour tester la connexion directement lors de l'accès à ce fichier.
// Assurez-vous de commenter ou supprimer cette section en environnement de production.
/*
$connection = connectDB();

if ($connection) {
    echo "Connexion à la base de données PostgreSQL '" . DB_NAME . "' réussie !";

    // Fermer la connexion de test
    pg_close($connection);

    // Vous pouvez effectuer d'autres tests ici, comme une simple requête.
    // $result = pg_query($connection, "SELECT NOW();");
    // if ($result) {
    //     $row = pg_fetch_row($result);
    //     echo "<br>Heure actuelle du serveur PostgreSQL : " . $row[0];
    // } else {
    //     echo "<br>Erreur lors de l'exécution de la requête de test : " . pg_last_error($connection);
    // }

} else {
    // Le message d'erreur détaillé est déjà loggué par connectDB()
    echo "Échec de la connexion à la base de données PostgreSQL '" . DB_NAME . "'. Veuillez vérifier les logs du serveur pour plus de détails.";
}
 */
?>
