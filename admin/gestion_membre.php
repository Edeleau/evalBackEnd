<?php

require_once('../inc/init.php');
$title = 'Gestion des membres';

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

$membres = execRequete('SELECT id_membre, pseudo , nom,prenom,email,civilite, statut FROM membre');

//modif de statut
if (isset($_POST["majstatut"]) && isSuperAdmin()) {
    switch ($_POST['majStatut']) {
        case 'membre':
            $_POST['majStatut'] = 0;
            break;
        case 'admin':
            $_POST['majStatut'] = 1;
            break;
        case 'superadmin':
            $_POST['majStatut'] = 2;
            break;
        default:
            $errors[] = 'Statut non valide';
            break;
    }
    execRequete("UPDATE membre SET 
          statut       = :statut
    WHERE id_membre = :id_membre", array(
        'statut'    => $_POST['majStatut'],
        'id_membre' => $_POST['id_membre']
    ));
    header('location:' . $_SERVER['PHP_SELF']);
}
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_membre']) && isAdmin()) {
    execRequete("DELETE FROM membre WHERE id_membre=:id_membre", [
        'id_membre' => $_GET['id_membre']
    ]);
    header("location:" . URL.'admin/gestion_membre.php');
    exit;
}
if (!empty($_POST)) {
    $errors = [];

    // Controles avant l'insertion en BDD
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) {
            $nb_champs_vides++;
        }
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

    // Controle du statut
    switch ($_POST['statut']) {
        case 'membre':
            $_POST['statut'] = 0;
            break;
        case 'admin':
            $_POST['statut'] = 1;
            break;
        case 'superadmin':
            $_POST['statut'] = 2;
            break;
        default:
            $errors[] = 'Statut non valide';
            break;
    }
    // controle de l'unicité du pseudo
    if (getMembreByPseudo($_POST['pseudo'])) {
        $errors[] = "Pseudo indisponible. Merci d'en choisir un autre";
    }

    if (empty($errors)) {
        // Aucune erreur, je peux procéder à l'inscription
        $_POST['mdp'] = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
        execRequete("INSERT INTO membre VALUES (NULL,:pseudo,:mdp,:nom,:prenom,:email,:civilite,:statut,NOW())", $_POST);


        header("location:" . $_SERVER['PHP_SELF']);
        exit();
    }
}
require_once('../inc/header.php');
?>
<h1>Gestion des membres</h1>
<hr>
<table class="table table-bordered table-striped table-responsive-xl mt-3">
    <tr>
        <th>ID membre</th>
        <th>Pseudo</th>
        <th>Nom</th>
        <th>Prenom</th>
        <th>Email</th>
        <th>Cicilite</th>
        <th>Statut</th>
        <th>Action</th>

    </tr>
    <?php
    while ($membre = $membres->fetch()) :
    ?>
        <tr>
            <td><?php echo $membre['id_membre'] ?></td>
            <td><?php echo $membre['pseudo'] ?></td>
            <td><?php echo $membre['nom'] ?></td>
            <td><?php echo $membre['prenom'] ?></td>
            <td><?php echo $membre['email'] ?></td>
            <td><?php echo $membre['civilite'] == 'm' ? 'Homme' : 'Femme' ?></td>
            <td>
                <?php if (isSuperAdmin() && $_SESSION['membre']['statut'] !== $membre['id_membre']) {
                ?>
                    <form method="POST">
                        <div class="row d-flex justify-content-around">
                            <input type="hidden" name="id_membre" value="<?php echo $membre['id_membre'] ?>">
                            <select name="majStatut" class="form-control w-75">
                                <option value="membre" <?php echo $membre['statut'] == 0 ? 'selected' : ''  ?>>Membre</option>
                                <option value="admin" <?php echo $membre['statut'] == 1 ? 'selected' : ''  ?>>Admin</option>
                                <option value="superadmin" <?php echo $membre['statut'] == 2 ? 'selected' : ''  ?>>SuperAdmin</option>
                            </select>
                            <button type="submit" name="majstatut" class="btn btn-primary btn-sm"><i class="fas fa-sync"></i></button>
                        </div>

                    </form>
                <?php 
                } else {
                    switch ($membre['statut']) {
                        case 0:
                            $statut = 'Membre';
                            break;
                        case 1:
                            $statut = 'Administrateur';
                            break;

                        case 2:
                            $statut = 'SuperAdministrateur';
                            break;
                    }
                    echo $statut;
                }
                ?>
            </td>
            <td class="row d-flex justify-content-around m-0 h-100  border-right-0 border-left-0 border-bottom-0">
                <a href="commande_membre.php?id_membre=<?php echo $membre['id_membre']?>&&action='view'"><i class="far fa-eye"></i></a>
                <a href="modif_membre.php?id_membre=<?php echo $membre['id_membre']?>"><i class="far fa-edit"></i></a>
                <?php if (((isAdmin()  && $membre['statut']< 1) || isSuperAdmin()) && $_SESSION['membre']['id_membre'] !== $membre['id_membre']) {?>

                <a href="?id_membre=<?php echo $membre['id_membre']?>&&action=delete" class="confirm"><i class="far fa-trash-alt"></i></a>
                <?php
                }
                ?>
            </td>
        </tr>
    <?php
    endwhile;
    ?>
</table>
<h1 class="mt-2">Ajout d'un membre</h1>
<hr>

<?php if (!empty($errors)) : ?>
    <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
<?php endif; ?>

<form method="post" class="pb-4">
    <fieldset>
        <div class="form-group row">
            <div class="col-6">
                <label for="pseudo">Pseudo</label>
                <input type="text" class="form-control 
            <?php
            echo (!empty($_POST) &&
                (empty(trim($_POST['pseudo'])) ||
                    iconv_strlen(trim($_POST['pseudo'])) < 2 ||
                    iconv_strlen(trim($_POST['pseudo'])) > 20)) ? 'is-invalid' : '' ?>" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo'] ??  '' ?>">
                <div class="invalid-feedback">
                    Merci de renseigner le pseudo. (2-20 caractères)
                </div>
            </div>
            <div class="col-6">
                <label for="mdp">Mot de passe</label><input type="password" class="form-control 
        <?php

        echo (!empty($_POST)
            && !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@\*])[\w$!\-\@\*]{8,20}$#', $_POST['mdp'])) ? 'is-invalid' : '' ?>" id="mdp" name="mdp">
                <div class="invalid-feedback">
                    Merci de saisir un mot de passe compris entre 8 et 20 caractères comportant au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractère spécial ($ ! - _ @ *)
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-6">

                <label for="email">Email</label>
                <input type="email" class="form-control 
            <?php echo (!empty($_POST)
                && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?php echo $_POST['email'] ??  '' ?>">
                <div class="invalid-feedback">
                    Merci de saisir une adresse mail valide
                </div>
            </div>
            <div class="col-6">
                <label for="statut">Statut</label>
                <select name="statut" id="statut" class="form-control">
                    <option value="membre"  <?php echo isset($_POST['statut']) == 'membre' ? 'selected' : ''  ?>>Membre</option>
                    <option value="admin" <?php echo isset($_POST['statut']) == 'admin' ? 'selected' : ''  ?>>Admin</option>
                    <option value="superadmin" <?php echo isset($_POST['statut']) == 'superadmin' ? 'selected' : ''  ?>>SuperAdmin</option>
                </select>
            </div>
        </div>



    </fieldset>
    <fieldset>
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

    <button type="submit" class="btn btn-primary">Enregistrer le membre</button>
</form>
<?php
require_once('../inc/footer.php');
