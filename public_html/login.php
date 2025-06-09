<?php include '../includes/header.php'; ?>
<!-- Specific JS for login.php - CSS is now in custom_style.css -->
<script src="assets/js/processLogin.js"></script>

<div class="container login-content-container">
    <section id="pinksquare">
      <!--Header-->
      <div id="welcome">
        <h1 id="lh1">GabonSonde - Accès aux Sondages</h1>

        <p>Veuillez vous connecter pour accéder aux sondages.
           Votre email est utilisé uniquement pour la connexion et ne sera pas partagé.</p>


           <!--Image-->
           <div class="databox">
             <img src="assets/images/root_imgs/cre.jpg" alt="Image connexion">
           </div>


           <!--Login email entry-->
          <form method="post" action="sondage.php" onsubmit="return validateEmail();">
            <div class="login-form-group">
                <label for="formEmail">Adresse Email:</label>
                <input type="email" class="form-control" id="formEmail" name="email" placeholder="exemple@domaine.com" value="" required/>
            </div>

            <div class="login-button-group mt-3">
                <button type="submit" class="btn btn-primary">Connexion</button>
                <button type="reset" class="btn btn-secondary">Effacer</button>
            </div>
          </form>


          <!--Display email entry error-->
          <div id="errorMsg"></div>

      </div> <!-- closes div#welcome -->
    </section> <!-- closes section#pinksquare -->
</div> <!-- closes login-content-container -->

<?php include '../includes/footer.php'; ?>
