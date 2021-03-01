<?php

require_once('inc/init.php');

$title = 'Compte';

if (!isConnected()) {
    header('location:' . URL . 'connexion.php');
    exit();
}
$commandes = execRequete('SELECT * FROM commande c
INNER JOIN membre m 
ON m.id_membre = c.id_membre
INNER JOIN produit p
ON c.id_produit = p.id_produit
INNER JOIN salle s
ON s.id_salle = p.id_salle
WHERE c.id_membre = :id_membre', [
    'id_membre' => $_SESSION['membre']['id_membre']
]);
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_commande']) && !empty($_GET['id_membre']) && !empty($_GET['id_produit']) && $_SESSION['membre']['id_membre'] === $_GET['id_membre']) {
    execRequete("UPDATE produit SET 
    etat       = 'libre'
    WHERE id_produit = :id_produit ", [
        'id_produit' => $_GET['id_produit']
    ]);
    execRequete("DELETE FROM commande WHERE id_commande=:id_commande", [
        'id_commande' => $_GET['id_commande']
    ]);
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

        $_POST['id_membre'] = $_SESSION['membre']['id_membre'];
        var_dump($_POST);
        execRequete("UPDATE membre SET 
              nom         = :nom,
              prenom      = :prenom,
              email       = :email,
              civilite    = :civilite
        WHERE id_membre   = :id_membre ", $_POST);

        $_SESSION['membre']['nom']         = $_POST["nom"];
        $_SESSION['membre']['prenom']      = $_POST["prenom"];
        $_SESSION['membre']['email']       = $_POST["email"];
        $_SESSION['membre']['civilite']    = $_POST["civilite"];
        $_SESSION['message']               = 'Coordonnées mise à jour !';

        header('location:' . $_SERVER['PHP_SELF']);
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
    if (!empty($_POST['mdpactuel']) && !password_verify($_POST['mdpactuel'], $_SESSION['membre']['mdp'])) {
        $errorsmdp[] = 'Mot de passe actuel incorrect';
    }
    if (!empty($_POST['newmdp']) && !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@\*])[\w$!\-\@\*]{8,20}$#', $_POST['newmdp'])) {
        $errorsmdp[] = 'Complexité du mot de passe non respectée';
    }
    if ($_POST['newmdp'] !== $_POST['confirmmdp']) {
        $errorsmdp[] = 'La confirmation ne concorde pas avec le nouveau mot de passe';
    }
    if ($_POST['newmdp'] === $_POST['mdpactuel']) {
        $errorsmdp[] = 'Le nouveau mot de passe doir être différent du mot de passe actuel';
    }

    if (empty($errorsmdp)) {
        $newmdp = password_hash($_POST['newmdp'], PASSWORD_DEFAULT);
        execRequete("UPDATE membre SET 
              mdp     = :newmdp
        WHERE id_membre   = :id_membre", [
            'newmdp'    => $newmdp,
            'id_membre' => $_SESSION['membre']['id_membre']
        ]);
        $_SESSION['membre']['mdp'] = $newmdp;
        $_SESSION['message2'] = 'Mot de passe changé avec succès';
        header('location:' . $_SERVER['PHP_SELF']);
        exit;
    }
}


require_once('inc/header.php');

?>
<div class="row">
    <div class="col-md-6 mt-5">

        <form method="post">
            <h2>Identifiants</h2>

            <p>Pseudo : <?php echo $_SESSION['membre']['pseudo'] ?> </p>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" name="email" id="email" class="form-control 
                <?php echo (!empty($_POST['email'])
                    && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" value="<?php echo $_POST['email'] ?? $_SESSION["membre"]['email'] ?>">
                <div class="invalid-feedback">
                    Merci de saisir une adresse mail valide
                </div>
            </div>
            <h2>Coordonnées</h2>
            <hr>
            <div class="form-row">
                <div class="form-group col-3">
                    <label for="civilite">Civilité</label>
                    <select name="civilite" id="civilite" class="form-control">
                        <option value="m">M</option>
                        <option value="f" <?php echo ((!empty($_POST['civilite']) && $_POST['civilite'] == 'f') || $_SESSION["membre"]['civilite'] == 'f') ? 'selected' : '' ?>>Mme</option>
                    </select>
                </div>
                <div class="form-group col">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $_POST['nom'] ??  $_SESSION["membre"]['nom'] ?>">
                </div>
                <div class="form-group col">
                    <label for="prenom">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $_POST['prenom'] ??  $_SESSION["membre"]['prenom'] ?>">
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
                <label for="mdpactuel">Mot de passe actuel</label>
                <input type="password" name="mdpactuel" id="mdpactuel" class="form-control">
            </div>
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
<h2 class="mt-3">Mes commandes</h2>

<table class="table table-bordered table-striped table-responsive-xl mt-2">
    <tr>
        <th>ID commande</th>
        <th>Produit</th>
        <th>Prix</th>
        <th>Date d'enregistrement</th>
        <th>Action</th>

    </tr>
    <?php
    while ($commande = $commandes->fetch()) :
        $commande['date_arrivee'] = (new DateTime($commande['date_arrivee']))->format('d/m/Y');
        $commande['date_depart'] = (new DateTime($commande['date_depart']))->format('d/m/Y');
    ?>
        <tr>
            <td><?php echo $commande['id_commande'] ?></td>
            <td class="text-center"><?php echo $commande['id_salle'] . '-' . $commande['titre'] ?> <br> <?php echo $commande['date_arrivee'] . ' au ' . $commande['date_depart'] ?></td>
            <td><?php echo $commande['prix'] ?> &euro;</td>
            <td><?php echo $commande['date_enregistrement'] ?></td>
            <td class="row d-flex justify-content-around m-0 h-100  border-right-0 border-left-0 border-bottom-0">
                <form action="" method="GET">

                    <a href="?id_commande=<?php echo $commande['id_commande'] ?>&&id_produit=<?php echo $commande['id_produit'] ?>&&id_membre=<?php echo $commande['id_membre'] ?>&&action=delete" class="confirm"><i class="far fa-trash-alt"></i></a>
                </form>
            </td>
        </tr>
    <?php
    endwhile;
    ?>
</table>
<?php

require_once('inc/footer.php');
