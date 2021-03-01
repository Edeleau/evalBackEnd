<?php
require_once('inc/init.php');

$title = 'Fiche produit';
if (!empty($_GET['id_produit'])) {
    $produit = execRequete("SELECT * FROM produit p
    INNER JOIN salle s
    ON s.id_salle = p.id_salle
    WHERE id_produit=:id_produit", [
        'id_produit' => $_GET['id_produit']
    ]);
    if ($produit->rowCount() === 0) {
        $errors[] = 'Salle innexistante <a href="' . URL . '"> Revenir a la boutique </a>';
    } else {
        $infos = $produit->fetch();
        $title .= ' : ' . $infos['titre'];
    }
} else {
    header('location:' . URL);
    exit;
}
if (!empty($_POST)) {
    if (isset($_POST['commande'])) {
        if ($infos['etat'] === 'libre') {
            execRequete("INSERT INTO commande VALUES (NULL,:id_membre,:id_produit,NOW())", [
                'id_membre' => $_SESSION['membre']['id_membre'],
                'id_produit' => $infos['id_produit']
            ]);
            execRequete("UPDATE produit SET etat   = 'reservation' WHERE id_salle  = :id_salle AND id_produit = :id_produit",[
                'id_salle' => $infos['id_salle'],
                'id_produit'=>$infos['id_produit']
            ]);
            $confirmCommande = 'Commande bien enregistrée';
        }else{
            $confirmCommande = 'Salle déjà réservée';
        }
    } else {
        $nb_champs_vides =  0;
        foreach ($_POST as $key => $value) {
            $_POST[$key] = htmlspecialchars(trim($value));
            if ($_POST[$key] === '') $nb_champs_vides++;
        }
        if (!isset($_POST['stars'])) {
            $nb_champs_vides++;
        }
        if ($nb_champs_vides > 0) {
            $errorsAvis[] = "Il manque $nb_champs_vides information(s)";
        }
        if (empty($errorsAvis)) {
            execRequete("INSERT INTO avis VALUES (NULL,:id_membre,:id_salle,:commentaire,:note,NOW())", [
                'id_membre' => $_SESSION['membre']['id_membre'],
                'id_salle' => $infos['id_salle'],
                'commentaire' => $_POST['avis'],
                'note' => $_POST['stars']
            ]);
        }

    }
}
require_once('inc/header.php');
if (!empty($errors)) : ?>
    <div class="alert alert-danger mt-5"><?php echo implode('<br>', $errors) ?></div>
<?php else : ?>
    <?php if (!empty($confirmCommande)) : ?>
        <div class="alert alert-success mt-5"><?php echo $confirmCommande ?></div>
    <?php endif; ?>
    <div class="row mt-4 ">
        <div class="col-8 col-md-9">
            <h2><?php echo $infos['titre'] ?></h2>
        </div>
        <div class="col-4 col-md-3">
            <?php if (isConnected()) : ?>
                <form action="" method="post" name="commande">
                    <button type="submit" name="commande" class="btn btn-success">Commander cette salle</button>
                </form>
            <?php else : ?>
                <a href="<?php URL ?>connexion.php" class="btn btn-success">Se connecter</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="row ">
        <div class="col-md-6 infoPhotoMap">
            <img src="<?php echo URL . 'photos/' . $infos['photo'] ?>" alt="<?php echo $infos['titre'] ?>" class="mw-100">
        </div>
        <div class="col-md-6 infoPhotoMap">
            <div class="h-50 position-relative">
                <p class="font-weight-bold">Description</p>
                <p><?php echo $infos['description'] ?></p>
            </div>
            <div class="h-50">
                <p class="font-weight-bold m-0 local">Localisation</p>
                <iframe class="w-100 h-100 pb-5 mt-2" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?hl=fr&amp;q=<?php echo $infos['adresse'] . '%20' . $infos['cp'] . '%20' . $infos['ville'] ?>&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
            </div>

        </div>
    </div>
    <p class="font-weight-bold mt-2">Informations complémentaires</p>
    <div class="row">
        <div class="col-md-4">
            <p><i class="fas fa-calendar-alt"></i> Date arrivée : <?php echo $infos['date_arrivee'] ?></p>
            <p><i class="fas fa-calendar-alt"></i> Date départ : <?php echo $infos['date_depart'] ?></p>
        </div>
        <div class="col-md-3">
            <p><i class="fas fa-users"></i> Capacité : <?php echo $infos['capacite'] ?></p>
            <p><i class="fas fa-inbox"></i> Catégorie : <?php echo $infos['categorie'] ?></p>
        </div>
        <div class="col-md-5">
            <p><i class="fas fa-map-marker-alt"></i> Adresse : <?php echo $infos['adresse'] . ', ' . $infos['cp'] . ', ' . $infos['ville'] ?></p>
            <p><i class="fas fa-euro-sign"></i></i> Prix : <?php echo $infos['prix'] ?>&euro;</p>
        </div>
    </div>
    <?php if (isConnected()) : ?>
        <?php if (!empty($errorsAvis)) : ?>
            <div class="alert alert-danger"><?php echo implode('<br>', $errorsAvis) ?></div>
        <?php endif; ?>
        <a id="avisBtn" class="btn btn-success mb-3">Déposer un avis</a>

        <div class="avisContainer">
            <form action="#" method="POST" name="avis" class="my-auto">
                <div class="form-group" id="avis">
                    <div class="rating">
                        <input name="stars" value="5" id="e5" type="radio"><label for="e5">☆</label>
                        <input name="stars" value="4" id="e4" type="radio"><label for="e4">☆</label>
                        <input name="stars" value="3" id="e3" type="radio"><label for="e3">☆</label>
                        <input name="stars" value="2" id="e2" type="radio"><label for="e2">☆</label>
                        <input name="stars" value="1" id="e1" type="radio"><label for="e1">☆</label>
                    </div>
                    <label for="avis">Laissez votre avis sur cette salle : </label>

                    <textarea class="form-control w-100" name="avis" rows="3"></textarea>
                    <button type="submit" class="btn btn-success mt-2">Envoyez votre avis</button>
                </div>
            </form>

        </div>
    <?php else : ?>
        <a href="<?php URL ?>connexion.php" class="btn btn-success">Se connecter</a>
    <?php endif; ?>
<?php endif; ?>

<?php

require_once('inc/footer.php');
