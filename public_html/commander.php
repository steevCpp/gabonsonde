<?php
session_start(); // Start session to access session messages
include '../includes/header.php';
?>

    <div class="container commander-specific-container"> <!-- Added specific class -->

        <?php
        // Display session messages if they exist
        if (isset($_SESSION['message'])) {
            $msg_type = $_SESSION['message']['type'] === 'success' ? 'alert-success' : 'alert-danger';
            echo '<div class="alert ' . $msg_type . '" role="alert">' . htmlspecialchars($_SESSION['message']['text']) . '</div>';
            unset($_SESSION['message']); // Clear the message after displaying it
        }
        ?>

        <h1>Commander un Sondage Personnalisé</h1>
        <p>Remplissez le formulaire ci-dessous pour nous faire part de vos besoins en matière de sondage. Notre équipe vous contactera sous peu pour discuter de votre projet et vous fournir un devis.</p>

        <form action="process_order.php" method="post">
            <h2>Vos Informations de Contact</h2>
            <div class="mb-3">
                <label for="nom_complet" class="form-label">Nom complet :</label>
                <input type="text" class="form-control" id="nom_complet" name="nom_complet" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Adresse e-mail :</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="telephone" class="form-label">Numéro de téléphone :</label>
                <input type="tel" class="form-control" id="telephone" name="telephone">
            </div>

            <div class="mb-3">
                <label for="organisation" class="form-label">Organisation/Entreprise (si applicable) :</label>
                <input type="text" class="form-control" id="organisation" name="organisation">
            </div>

            <h2>Détails de Votre Sondage</h2>
            <div class="mb-3">
                <label for="sujet_sondage" class="form-label">Sujet principal du sondage :</label>
                <input type="text" class="form-control" id="sujet_sondage" name="sujet_sondage" required>
            </div>

            <div class="mb-3">
                <label for="public_cible" class="form-label">Public cible :</label>
                <textarea class="form-control" id="public_cible" name="public_cible" placeholder="Décrivez le public que vous souhaitez sonder (ex: âge, lieu, intérêts, etc.)" required rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label for="nombre_questions" class="form-label">Nombre approximatif de questions :</label>
                <input type="number" class="form-control" id="nombre_questions" name="nombre_questions" min="1">
            </div>

            <div class="mb-3">
                <label for="objectifs" class="form-label">Objectifs du sondage :</label>
                <textarea class="form-control" id="objectifs" name="objectifs" placeholder="Quels sont les principaux objectifs que vous souhaitez atteindre avec ce sondage ?" required rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label for="budget" class="form-label">Budget approximatif (FCFA, si connu) :</label>
                <input type="text" class="form-control" id="budget" name="budget">
            </div>

            <div class="mb-3">
                <label for="autres_informations" class="form-label">Autres informations ou exigences spécifiques :</label>
                <textarea class="form-control" id="autres_informations" name="autres_informations" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Envoyer ma Demande</button>
        </form>
    </div>

<?php include '../includes/footer.php'; ?>
