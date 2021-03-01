<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique | <?php echo $title ?> </title>
    <!-- bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <!-- css fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <!-- CSS datepicker -->
    <link rel="stylesheet" href="<?php echo URL ?>node_modules/vanillajs-datepicker/dist/css/datepicker-bs4.min.css">
    <!-- CSS timepicker -->
    <link  href="<?php echo URL ?>node_modules/pickerjs/dist/picker.css" rel="stylesheet">

    <!-- Feuille de style -->
    <link rel="stylesheet" href="<?php echo URL ?>inc/css/style.css">
    <!-- script bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
    <!-- script perso -->
    <script src="<?php echo URL ?>inc/js/functions.js"></script>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
            <a class="navbar-brand" href="<?php echo URL ?>">ROOM</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item <?php echo($title =='Accueil') ? 'active':'' ?>">
                        <a class="nav-link" href="<?php echo URL ?>">Page d'accueil<span class="sr-only">(current)</span></a>
                    </li>
                    
                    <!-- Membre non connecté -->
                    <?php if(!isConnected()):?>
                    <li class="nav-item <?php echo($title =='Inscription') ? 'active':'' ?>">
                        <a class="nav-link" href="<?php echo URL ?>inscription.php">Inscription</a>
                    </li>
                    <li class="nav-item <?php echo($title =='Connexion') ? 'active':'' ?>">
                        <a class="nav-link" href="<?php echo URL ?>connexion.php">Connexion</a>
                    </li>
                    <?php endif; ?>

                    <!-- Membre connecté -->
                    <?php if(isConnected()):?>

                    <li class="nav-item <?php echo($title =='Compte') ? 'active':'' ?>">
                        <a class="nav-link" href="<?php echo URL ?>compte.php">Mon compte</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="<?php echo URL ?>connexion.php?action=deconnexion">Se déconnecter</a>
                    </li>
                    <?php endif; ?>

                    <!-- Administrateur -->
                    <?php if(isAdmin()):?>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="menuadmin" data-toggle="dropdown">Admin</a>
                        <div class="dropdown-menu" aria-labelledby="menuadmin">
                            <a href="<?php echo URL ?>admin/gestion_salle.php" class="dropdown-item">Gestion des salles</a>
                            <a href="<?php echo URL ?>admin/gestion_membre.php" class="dropdown-item">Gestion des membres</a>
                            <a href="<?php echo URL ?>admin/gestion_produit.php" class="dropdown-item">Gestion des produits</a>
                            <a href="<?php echo URL ?>admin/gestion_commande.php" class="dropdown-item">Gestion des commandes</a>
                            <a href="<?php echo URL ?>admin/gestion_avis.php" class="dropdown-item">Gestion des avis</a>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">