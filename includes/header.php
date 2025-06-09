<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html lang="fr">
<!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>GabonSonde</title>

<!-- jQuery (DOIT être avant custom.js) -->
<script src="/gabonsonde/assets/plugins/jquery-1.10.2.js"></script>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<!-- Custom JS (doit venir après jQuery) -->
<script src="/gabonsonde/assets/js/home_assets_js/custom.js"></script>

    <!-- Google Fonts (Roboto, Lato, Open Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Lato:wght@300;400;700;900&family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome (keeping existing) -->
    <link href="/gabonsonde/assets/css/home_assets_css/font-awesome.min.css" rel="stylesheet" />
    <link href="/gabonsonde/assets/css/home_assets_css/font-awesome-animation.css" rel="stylesheet" />

    <!-- New Custom Stylesheet -->
    <link href="/gabonsonde/assets/css/custom_style.css" rel="stylesheet" />

    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body >
<!-- START NEW NAVBAR SECTION -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark-green fixed-top">
        <div class="container-fluid"> <!-- Changed to container-fluid for full width, or use .container for fixed width -->
            <a class="navbar-brand" href="/gabonsonde/index.php">
		<img src="/gabonsonde/assets/images/home_assets_img/logo.JPG" alt="GabonSonde Logo" style="height: 40px; border-radius: 50%;">
                <span class="ms-2">GabonSonde</span> </a>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavGabonSonde" aria-controls="navbarNavGabonSonde" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavGabonSonde">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" aria-current="page" href="/gabonsonde/index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'sondage.php') ? 'active' : ''; ?>" href="/gabonsonde/sondage.php">Participer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'resultats.php') ? 'active' : ''; ?>" href="/gabonsonde/resultats.php">Résultats</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'commander.php') ? 'active' : ''; ?>" href="/gabonsonde/commander.php">Commander</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'login.php') ? 'active' : ''; ?>" href="/gabonsonde/login.php">Connexion</a>
                    </li>
                    <!-- Add other links like admin if needed, or handle admin access differently -->
                </ul>
            </div>
        </div>
    </nav>
    <!-- END NEW NAVBAR SECTION -->

    <!-- Placeholder to adjust content due to fixed-top navbar -->
    <div style="padding-top: 70px;"></div>
