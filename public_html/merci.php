<?php
session_start();

// Configuration (pourrait être dans un fichier de config global si plus complexe)
$pageTitle = "Merci"; // Utilisé par header.php

// Inclure le header
// Assumant que header.php est dans le dossier 'includes' au même niveau que 'public_html'
// ou que le chemin d'inclusion de PHP est configuré pour le trouver.
// Si 'includes' est un sous-dossier de 'public_html', le chemin serait 'includes/header.php'
// Si 'includes' est un dossier parent de 'public_html', le chemin serait '../includes/header.php'
// Pour cet exemple, nous allons supposer que 'includes' est un dossier parent.
require_once '../includes/header.php'; // This path is already correct
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2 text-center">

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert <?php echo ($_SESSION['message']['type'] === 'success' ? 'alert-success' : 'alert-danger'); ?>" role="alert">
                    <?php
                    echo htmlspecialchars($_SESSION['message']['text']);
                    unset($_SESSION['message']); // Supprimer le message après l'affichage
                    ?>
                </div>
            <?php else: ?>
                <div class="alert alert-success" role="alert">
                    <h1>Merci !</h1>
                    <p class="lead">Votre action a été enregistrée avec succès.</p>
                </div>
            <?php endif; ?>

            <hr class="my-4">

            <p>Vous pouvez maintenant retourner à la liste des sondages ou visiter notre page d'accueil.</p>
            <a href="sondage.php" class="btn btn-primary btn-lg mt-3">Voir les sondages</a>
            <a href="index.php" class="btn btn-secondary btn-lg mt-3">Page d'accueil</a>

        </div>
    </div>
</div>

<?php
// Inclure le footer
require_once '../includes/footer.php'; // This path is already correct
?>
