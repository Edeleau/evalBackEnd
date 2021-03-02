<?php

require_once('../inc/init.php');
$title = 'Gestion des salles';

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_salle']) && is_numeric($_GET['id_salle'])) {
    //Récupération du salle en mbb pour en obtenir le nom du fichier de la photo
    $salle_asup = execRequete("SELECT photo FROM salle WHERE id_salle=:id_salle", ['id_salle' => $_GET['id_salle']]);
    if ($salle_asup->rowCount() == 1) {
        $info = $salle_asup->fetch();
        $photo = $info['photo'];

        // suppression du fichier photo
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $photo)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $photo);
        }
        //suppression du salle en BDD
        execRequete("DELETE FROM salle WHERE id_salle=:id_salle", [
            'id_salle' => $_GET['id_salle']
        ]);
        header('location:' . $_SERVER['PHP_SELF']);
        exit;
    }
}
// Traitement du formulaire
if (!empty($_POST)) {
    $nb_champs_vides =  0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(trim($value));
        if ($_POST[$key] == '') $nb_champs_vides++;
    }

    if (empty($_FILES['photo']['name'])) {
        if (empty($_POST['photo_actuelle'])) {
            $nb_champs_vides++;
        }
    } else {
        $formatAutorise = array('image/jpeg', 'image/png', 'image/webp');
        if (!in_array($_FILES['photo']['type'], $formatAutorise)) {
            $errors[] = 'Format incorrect : ' . $_FILES['photo']['type'] . '<br>Fichiers JPEG, PNG, et WEBP seulement.';
        }
    }
    // controle du code postal
    if (!preg_match('#^[0-9]{4,5}$#', $_POST['cp'])) {
        $errors[] = 'Code postal invalide';
    }
    if ($nb_champs_vides > 0) {
        $errors[] = "Il manque $nb_champs_vides information(s)";
    }

    if (empty($errors)) {
        if (!empty($_FILES['photo']['name'])) {
            if (isset($_POST['photo_actuelle']) && file_exists($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $_POST['photo_actuelle'])) {
                # suppression du fichier
                unlink($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $_POST['photo_actuelle']);
            }
            // gérer la photo (copie physique du fichier)
            $extension = (new SplFileInfo($_FILES['photo']['name']))->getExtension();
            $nomPhotoBDD = str_replace(' ', '', $_POST['titre']) . '_' . date("YmdHis") . '.' . $extension;
            $dossierPhotos = $_SERVER['DOCUMENT_ROOT'] . URL . 'photos/';
            move_uploaded_file($_FILES['photo']['tmp_name'], $dossierPhotos . $nomPhotoBDD);
        } else {
            $nomPhotoBDD = $_POST['photo_actuelle'];
        }
        unset($_POST['photo_actuelle']);
        $_POST['photo'] = $nomPhotoBDD;

        if (isset($_POST['id_salle'])) {
            //update
            execRequete("UPDATE salle 
                        SET
                        titre       = :titre,
                        description = :description,
                        photo       = :photo,
                        pays        = :pays,
                        ville       = :ville,
                        adresse     = :adresse,
                        cp          = :cp,
                        capacite    = :capacite,
                        categorie   = :categorie
                        WHERE 
                        id_salle  = :id_salle", $_POST);
        } else {
            // Insertion en BDD
            execRequete("INSERT INTO salle VALUES (NULL,:titre,:description,:photo,:pays,:ville,:adresse,:cp,:capacite,:categorie)", $_POST);
        }
        //on force le mode affichage des salle
        $_GET['action'] = 'affichage';
    }
}


require_once('../inc/header.php');
?>
<h1>Gestion des salles</h1>
<hr>

<ul class="nav nav-tabs nav-justified">
    <li class="nav-item"><a class="nav-link <?php echo (!isset($_GET['action'])
                                                || (isset($_GET['action']) && $_GET['action'] == 'affichage')) ? 'active' : '' ?>" href="?action=affichage">Affichage des salles</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (isset($_GET['action']) && (($_GET['action'] == 'ajout') || ($_GET['action'] == 'edit'))) ? 'active' : '' ?>" href="?action=ajout">Ajouter une salle</a></li>
</ul>

<?php

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) {

    // Affichage des salles
    $order = '';
    $testOrder = ['id_salle', 'titre', 'description', 'photo', 'pays', 'ville', 'adresse','cp','capacite','categorie'];
    if (isset($_GET['order']) &&  in_array($_GET['order'], $testOrder)) {
        $order = 'ORDER BY ' . $_GET['order'];
    }
    $resultats = execRequete('SELECT * FROM salle '. $order);
    if ($resultats->rowCount() == 0) {
?>
        <div class="alert alert-info mt-5">Il n'y a pas encore de salles enregistrés</div>
    <?php
    } else {
    ?>
        <table class="table table-bordered table-striped table-responsive mt-3">
            <tr>
                <?php
                // les entêtes de colonne
                for ($i = 0; $i < $resultats->columnCount(); $i++) {
                    $colonne = $resultats->getColumnMeta($i);
                    // var_dump(get_class_methods($resultats));
                ?>
                    <th><a href="?order=<?php echo ($colonne['name']) ?>"><?php echo ucfirst($colonne['name']) ?></a></th>
                <?php
                }
                ?>
                <th colspan="2">Actions</th>
            </tr>
            <?php
            // Les données
            while ($ligne = $resultats->fetch()) {
            ?>
                <tr>
                    <?php

                    foreach ($ligne as $key => $value) {
                        switch ($key) {
                            case 'photo':
                                $value = '<img class="img-fluid" src="' . URL . 'photos/' . $value . '" alt="' . $ligne['titre'] . '">';
                                break;
                            case 'description':
                                $extrait = (iconv_strlen($value) > 30) ? substr($value, 0, 30) : $value;
                                if ($extrait != $value) {
                                    $lastSpace = strrpos($extrait, ' ');
                                    $value = substr($extrait, 0, $lastSpace) . '...';
                                }
                                break;
                            default:
                                # code...
                                break;
                        }

                    ?>
                        <td><?php echo $value ?></td>
                    <?php
                    }
                    ?>
                    <td><a href="?action=edit&id_salle=<?php echo $ligne['id_salle'] ?>"><i class="fas fa-edit"></i></a></td>
                    <td><a href="?action=delete&id_salle=<?php echo $ligne['id_salle'] ?>" class="confirm"><i class="fas fa-trash-alt"></i></a></td>
                </tr>
            <?php
            }
            ?>
        </table>
    <?php
    }
}

if (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) {
    if ($_GET['action'] == 'edit' && !empty($_GET['id_salle']) && is_numeric($_GET['id_salle'])) {
        $resultats = execRequete(
            "SELECT * FROM salle WHERE id_salle = :id_salle",
            array(
                'id_salle' => $_GET['id_salle']
            )
        );
        $salle_courante = $resultats->fetch();
    }
    // Formulaire d'edition de salle
    ?>
    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="py-5">
        <?php if (!empty($salle_courante['id_salle'])) : ?>
            <input type="hidden" name='id_salle' value="<?php echo $salle_courante['id_salle'] ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="titre">Titre</label>
                <input type="text" class="form-control" id="titre" name="titre" value="<?php echo $_POST['titre'] ?? $salle_courante['titre'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="categorie">Pays</label>
                <select name="pays" id="pays" class="form-control w-100">
                    <option value="france" <?php echo (isset($salle_courante) && $salle_courante['pays'] == 'france') ? 'selected' : ''  ?>>France</option>
                    <option value="belgique" <?php echo (isset($salle_courante) && $salle_courante['pays'] == 'belgique') ? 'selected' : ''  ?>>Belgique</option>
                    <option value="espagne" <?php echo (isset($salle_courante) && $salle_courante['pays'] == 'espagne') ? 'selected' : ''  ?>>Espagne</option>
                </select>
            </div>
        </div>
        <div class="form-row">

            <div class="form-group col-md-6">
                <label for="ville">Ville</label>
                <input type="text" class="form-control" id="ville" name="ville" value="<?php echo $_POST['ville'] ?? $salle_courante['ville'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="adresse">Adresse</label>
                <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo $_POST['adresse'] ?? $salle_courante['adresse'] ?? '' ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="cp">Code Postal</label>
                <input type="number" class="form-control" id="cp" name="cp" value="<?php echo $_POST['cp'] ?? $salle_courante['cp'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="capacite">Capacité</label>
                <input type="number" class="form-control" id="capacite" name="capacite" value="<?php echo $_POST['capacite'] ?? $salle_courante['capacite'] ?? '' ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="categorie">Catégorie</label>
                <select class="form-control" id="categorie" name="categorie">

                    <?php
                    $categories = array('réunion', 'bureau', 'formation');
                    foreach ($categories as $categorie) {
                    ?>
                        <option <?php echo (
                                    (isset($_POST['categorie']) && ($_POST['categorie'] == $categorie))
                                    ||
                                    (isset($salle_courante['categorie']) && $salle_courante['categorie'] == $categorie)) ? 'selected' : '' ?>>
                            <?php echo ($categorie) ?>
                        </option>

                    <?php
                    }

                    ?>
                </select>

            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="7"><?php echo $_POST['description'] ?? $salle_courante['description'] ?? '' ?></textarea>
        </div>


        <div class="form-group">
            <label for="photo"><i class="fas fa-camera-retro iconephoto"></i></label>
            <input type="file" class="form-control d-none" id="photo" name="photo" accept="image/jpeg,image/pnj,image/webp">
            <div id="preview">
                <?php
                if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($salle_courante['photo'])) {
                ?>
                    <img src="<?php echo URL . 'photos/' . $salle_courante['photo'] ?>" alt="<?php echo $salle_courante['titre'] ?>" class="img-fluid vignette" id="placeholder">
                <?php
                } else {
                ?>
                    <img src="<?php echo URL . 'img/placeholder.png' ?>" alt="placeholder" class="img-fluid vignette" id="placeholder">
                <?php
                }
                ?>

            </div>
            <?php
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($salle_courante['photo'])) {
            ?>
                <input type="hidden" name="photo_actuelle" value="<?php echo $salle_courante['photo'] ?>">
            <?php
            }
            ?>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>

    </form>

<?php

}

require_once('../inc/footer.php');
