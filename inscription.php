<?php

require_once('inc/init.php');

$title = 'Inscription';

if (!empty($_POST)) {

    $errors = array();

    // Controles avant l'insertion en BDD
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errors[] = "Il manque $nb_champs_vides information(s)";
    }

    // controle le pseudo
    if (
        iconv_strlen(trim($_POST['pseudo'])) < 2 ||
        iconv_strlen(trim($_POST['pseudo'])) > 20
    ) {
        $errors[] = 'Pseudo invalide';
    }
    // controle du mot de passe
    if (!preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@\*])[\w$!\-\@\*]{8,20}$#', $_POST['mdp'])) {
        $errors[] = 'Complexité du mot de passe non respectée';
    }

    // controle de l'email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format de mail invalide';
    }


    // controle de l'unicité du pseudo
    if (getMembreByPseudo($_POST['pseudo'])) {
        $errors[] = "Pseudo indisponible. Merci d'en choisir un autre";
    }

    if (empty($errors)) {
        // Aucune erreur, je peux procéder à l'inscription
        $_POST['mdp'] = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
        execRequete("INSERT INTO membre VALUES (NULL,:pseudo,:mdp,:nom,:prenom,:email,:civilite,0,NOW())", $_POST);

        $_SESSION['membre'] = getMembreByPseudo($_POST['pseudo'])->fetch();

        header("location:" . URL . 'compte.php');
        exit();
    }
}






require_once('inc/header.php');

?>

<h1 class="mt-2">Inscription</h1>
<hr>

<?php if (!empty($errors)) : ?>
    <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
<?php endif; ?>

<form method="post" class="pb-4">
    <fieldset>
        <legend>Identifiants</legend>

        <div class="form-group">
            <label for="pseudo">Pseudo</label>
            <input type="text" class="form-control 
            <?php
            // trim() retire les espaces au debut et en fin de chaine
            echo (!empty($_POST) &&
                (empty(trim($_POST['pseudo'])) ||
                    iconv_strlen(trim($_POST['pseudo'])) < 2 ||
                    iconv_strlen(trim($_POST['pseudo'])) > 20)) ? 'is-invalid' : '' ?>" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo'] ??  '' ?>">
            <div class="invalid-feedback">
                Merci de renseigner le pseudo. (2-20 caractères)
            </div>
        </div>

        <div class="form-group"><label for="mdp">Mot de passe</label><input type="password" class="form-control 
        <?php
      
        echo (!empty($_POST)
            && !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@\*])[\w$!\-\@\*]{8,20}$#', $_POST['mdp'])) ? 'is-invalid' : '' ?>" id="mdp" name="mdp">
            <div class="invalid-feedback">
                Merci de saisir un mot de passe compris entre 8 et 20 caractères comportant au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractère spécial ($ ! - _ @ *)
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control 
            <?php echo (!empty($_POST)
                && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?php echo $_POST['email'] ??  '' ?>">
            <div class="invalid-feedback">
                Merci de saisir une adresse mail valide
            </div>
        </div>

    </fieldset>
    <fieldset>
        <legend>Information personnelles</legend>
        <div class="form-row">
            <div class="form-group col-3">
                <label for="civilite">Civilité</label>
                <select name="civilite" id="civilite" class="form-control">
                    <option value="m">M</option>
                    <option value="f" <?php echo (!empty($_POST) && $_POST['civilite'] == 'f') ? 'selected' : '' ?>>Mme</option>
                </select>
            </div>
            <div class="form-group col">
                <label for="nom">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $_POST['nom'] ??  '' ?>">
            </div>
            <div class="form-group col">
                <label for="prenom">Prénom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $_POST['prenom'] ??  '' ?>">
            </div>

        </div>

    </fieldset>

    <button type="submit" class="btn btn-primary">S'inscrire</button>

</form>
<?php

require_once('inc/footer.php');
