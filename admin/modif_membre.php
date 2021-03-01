<?php

require_once('../inc/init.php');

$title = 'Modif compte';
$id_membre = $_GET['id_membre'];
$profil = execRequete("SELECT * FROM membre WHERE id_membre = :id_membre", [
    'id_membre' => $id_membre
])->fetch();
if (!isAdmin() || empty($id_membre)) {
    header('location:' . URL . 'index.php');
    exit();
}
if (isset($_POST['modifcoord'])) {
    unset($_POST['modifcoord']);
    $errorscoord = array();

    // Controles avant l'insertion en BDD
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errorscoord[] = "Il manque $nb_champs_vides information(s)";
    }

    if (empty($errorscoord)) {

        $_POST['id_membre'] = $_GET['id_membre'];
        var_dump($_POST);
        execRequete("UPDATE membre SET 
              nom         = :nom,
              prenom      = :prenom,
              email       = :email,
              civilite    = :civilite
        WHERE id_membre   = :id_membre ", $_POST);

        $_SESSION['message'] = 'Infos personnelles changé avec succès';

        header('location:' . URL . '/admin/gestion_membre.php');
        exit;
    }
}
if (isset($_POST['modifmdp'])) {
    //formulaire de mise a jour de changement de mot de passe
    unset($_POST['modifmdp']);
    $errorsmdp = array();
    // Controles avant l'insertion en BDD
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errorsmdp[] = "Il manque $nb_champs_vides information(s)";
    }
    if (!empty($_POST['newmdp']) && !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@\*])[\w$!\-\@\*]{8,20}$#', $_POST['newmdp'])) {
        $errorsmdp[] = 'Complexité du mot de passe non respectée';
    }
    if ($_POST['newmdp'] !== $_POST['confirmmdp']) {
        $errorsmdp[] = 'La confirmation ne concorde pas avec le nouveau mot de passe';
    }

    if (empty($errorsmdp)) {
        $newmdp = password_hash($_POST['newmdp'], PASSWORD_DEFAULT);
        execRequete("UPDATE membre SET 
              mdp     = :newmdp
        WHERE id_membre   = :id_membre", [
            'newmdp'    => $newmdp,
            'id_membre' => $_GET['id_membre']
        ]);
        $_SESSION['message2'] = 'Mot de passe changé avec succès';
        header('location:' . URL . '/admin/gestion_membre.php');
        exit;
    }
}


require_once('../inc/header.php');

?>
<div class="row">
    <div class="col-md-6 mt-5">

        <form method="post">
            <h2>Identifiants</h2>

            <p>Pseudo : <?php echo $profil['pseudo'] ?> </p>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" name="email" id="email" class="form-control 
                <?php echo (!empty($_POST['email'])
                    && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" value="<?php echo $_POST['email'] ?? $profil['email'] ?>">
                <div class="invalid-feedback">
                    Merci de saisir une adresse mail valide
                </div>
            </div>
            <h2>Information personnelles</h2>
            <hr>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="civilite">Civilité</label>
                    <select name="civilite" id="civilite" class="form-control">
                        <option value="m">M</option>
                        <option value="f" <?php echo ((!empty($_POST['civilite']) && $_POST['civilite'] == 'f') || $profil['civilite'] == 'f') ? 'selected' : '' ?>>Mme</option>
                    </select>
                </div>
                <div class="form-group col">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $_POST['nom'] ??  $profil['nom'] ?>">
                </div>
                <div class="form-group col">
                    <label for="prenom">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $_POST['prenom'] ??  $profil['prenom'] ?>">
                </div>

            </div>

            <?php if (!empty($errorscoord)) : ?>
                <div class="alert alert-danger"><?php echo implode('<br>', $errorscoord) ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['message'])) : ?>
                <div class="alert alert-success"><?php echo $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']);
            endif; ?>
            <button type="submit" class="btn btn-primary mb-3" name="modifcoord">Modifier mes coordonnées</button>
        </form>
    </div>
    <div class="col-md-6 mt-5">
        <h2>Changement de mot de passe</h2>
        <hr>
        <form method="post">
            <div class="form-group">
                <label for="newmdp">Nouveau de mot de passe</label>
                <input type="password" name="newmdp" id="newmdp" class="form-control">
            </div>
            <div class="form-group">
                <label for="confirmmdp">Confirmation nouveau mot de passe</label>
                <input type="password" name="confirmmdp" id="confirmmdp" class="form-control">
            </div>
            <?php if (!empty($errorsmdp)) : ?>
                <div class="alert alert-danger"><?php echo implode('<br>', $errorsmdp) ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['message2'])) : ?>
                <div class="alert alert-success"><?php echo $_SESSION['message2'] ?></div>
            <?php unset($_SESSION['message2']);
            endif; ?>
            <button type="submit" class="btn btn-primary mb-3" name="modifmdp">Modifier le mot de passe</button>
        </form>
    </div>
</div>

<?php

require_once('../inc/footer.php');
