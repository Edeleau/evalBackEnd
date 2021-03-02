<?php

require_once('../inc/init.php');
$title = 'Gestion des Produit';

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
    //suppression du produit en BDD
    execRequete("DELETE FROM produit WHERE id_produit=:id_produit", [
        'id_produit' => $_GET['id_produit']
    ]);
    header('location:' . $_SERVER['PHP_SELF']);
    exit;
}
// Traitement du formulaire
if (!empty($_POST)) {
    $nb_champs_vides =  0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(trim($value));
        if ($_POST[$key] === '') $nb_champs_vides++;
    }


    if ($nb_champs_vides > 0) {
        $errors[] = "Il manque $nb_champs_vides information(s)";
    }

    // Vérif si la salle est déjà réservé pour éviter la modification si un client l'a déjà commandé
    if (isset($_POST['id_produit'])) {
        $etat = execRequete('SELECT etat FROM produit WHERE id_produit = :id_produit', [
            'id_produit' => $_POST['id_produit']
        ])->fetch();
        if ($etat['etat'] === 'reservation') {
            $errors[] = "Salle déjà réservé , impossible de l'éditer";
        }
    }

    // Formatage des dates
    $date_arrivee =  DateTime::createFromFormat('d-m-Y', $_POST['range-start']);
    $date_depart = DateTime::createFromFormat('d-m-Y', $_POST['range-end']);
    if (false !== $date_arrivee  && false !== $date_depart) {
        $date_arrivee = $date_arrivee->format('Y-m-d');
        $date_depart = $date_depart->format('Y-m-d');
        // Vérif si les salles ne sont pas déjà réservé 
        $dispoSalle = execRequete(
            "SELECT COUNT(id_produit) AS result FROM produit p
             INNER JOIN salle s 
             ON s.id_salle = p.id_salle 
             WHERE p.id_salle = :id_salle AND
             date_depart >= :date_arrivee AND 
             date_arrivee <= :date_depart",
            [
                'id_salle' => $_POST['id_salle'],
                'date_arrivee' => $date_arrivee,
                'date_depart' => $date_depart
            ]
        )->fetch();
        if ($dispoSalle['result'] > 0) {
            $errors[] = "Salle déjà sur un autre produit sur ces dates, impossible de la créer/éditer";
        }
    } else {
        $errors[] = 'Date au mauvais format';
    }

    if (empty($errors)) {

        $_POST['date_arrivee'] = $date_arrivee . ' ' . $_POST['rangeTime-start'] . ':00';
        $_POST['date_depart'] = $date_depart . ' ' . $_POST['rangeTime-end'] . ':00';

        unset($_POST['rangeTime-start'], $_POST['rangeTime-end'], $_POST['range-end'], $_POST['range-start']);
        if (isset($_POST['id_produit'])) {
            //update
            execRequete("UPDATE produit 
                        SET
                        id_salle     = :id_salle,
                        date_arrivee = :date_arrivee,
                        date_depart  = :date_depart,
                        prix         = :prix
                        WHERE 
                        id_produit  = :id_produit", $_POST);
        } else {
            // Insertion en BDD
            execRequete("INSERT INTO produit VALUES (NULL,:id_salle,:date_arrivee,:date_depart,:prix,'libre')", $_POST);
        }
        //on force le mode affichage des produit
        header('location:' . URL . 'admin/gestion_produit?action=affichage');
    }
}
require_once('../inc/header.php');
?>
<h1>Gestion des produits</h1>
<hr>

<ul class="nav nav-tabs nav-justified">
    <li class="nav-item"><a class="nav-link <?php echo (!isset($_GET['action'])
                                                || (isset($_GET['action']) && $_GET['action'] == 'affichage')) ? 'active' : '' ?>" href="?action=affichage">Affichage des produits</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (isset($_GET['action']) && (($_GET['action'] == 'ajout') || ($_GET['action'] == 'edit'))) ? 'active' : '' ?>" href="?action=ajout">Ajouter une produit</a></li>
</ul>

<?php

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) {

    // Affichage des produits
    $order = '';
    $testOrder = ['id_produit', 'id_salle', 'date_arrivee', 'date_depart', 'prix', 'etat'];
    if (isset($_GET['order']) &&  in_array($_GET['order'], $testOrder)) {
        $order = 'ORDER BY ' . $_GET['order'];
    }
    $resultats = execRequete('SELECT id_produit , p.id_salle , titre, photo  , date_arrivee , date_depart, prix, etat FROM produit p
    INNER JOIN salle s
    WHERE p.id_salle = s.id_salle '.$order);
    if ($resultats->rowCount() == 0) {
?>
        <div class="alert alert-info mt-5">Il n'y a pas encore de produits enregistrés</div>
    <?php
    } else {
    ?>
        <table class="table table-bordered table-striped table-responsive mt-3 ">
            <tr class="text-center">
                <?php
                // les entêtes de colonne
                for ($i = 0; $i < $resultats->columnCount(); $i++) {

                    $colonne = $resultats->getColumnMeta($i);
                    if ($colonne['name'] == 'id_salle') {
                ?>
                        <th class="w-17"><a href="?order=<?php echo($colonne['name'])?>"> <?php echo ucfirst($colonne['name']);
                                            $i += 2 ?></a></th>
                    <?php
                    } else {
                    ?>

                        <th class="w-17"><a href="?order=<?php echo($colonne['name'])?>"><?php echo ucfirst($colonne['name']) ?></a></th>
                <?php
                    }
                }
                ?>
                <th colspan="2">Actions</th>
            </tr>
            <?php
            // Les données
            while ($ligne = $resultats->fetch()) {
            ?>

                <tr class="text-center">
                    <td><?php echo ($ligne['id_produit']) ?></td>
                    <td><?php echo ($ligne['id_salle'] . '-' . $ligne['titre'] . '<br><img class="img-fluid" src="' . URL . 'photos/' . $ligne['photo'] . '" alt="' . $ligne['titre'] . '">') ?></td>
                    <td><?php echo ($ligne['date_arrivee']) ?></td>
                    <td><?php echo ($ligne['date_depart']) ?></td>
                    <td><?php echo (number_format($ligne['prix'], 0, ',', '&nbsp')) ?> &euro;</td>
                    <td><?php echo ($ligne['etat']) ?></td>
                    <td><a href="?action=edit&id_produit=<?php echo $ligne['id_produit'] ?>"><i class="fas fa-edit"></i></a></td>
                    <td><a href="?action=delete&id_produit=<?php echo $ligne['id_produit'] ?>" class="confirm"><i class="fas fa-trash-alt"></i></a></td>
                </tr>
            <?php
            }
            ?>
        </table>
    <?php
    }
}
if (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) {
    if ($_GET['action'] == 'edit' && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
        $resultats = execRequete(
            "SELECT * FROM produit WHERE id_produit = :id_produit",
            array(
                'id_produit' => $_GET['id_produit']
            )
        );
        $produit_courant = $resultats->fetch();
        $dateArrivee = (new DateTime($produit_courant['date_arrivee']))->format('d-m-Y');
        $dateDepart = (new DateTime($produit_courant['date_depart']))->format('d-m-Y');
        $heureArrivee = (new DateTime($produit_courant['date_arrivee']))->format('H:i');
        $heureDepart = (new DateTime($produit_courant['date_depart']))->format('H:i');
    }
    $salles = execRequete(
        "SELECT * FROM salle"
    )->fetchAll();
    ?>
    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger mt-3"><?php echo implode('<br>', $errors) ?></div>
    <?php endif; ?>
    <form method="post" class="py-5">
        <?php if (!empty($produit_courant['id_produit'])) : ?>
            <input type="hidden" name='id_produit' value="<?php echo $produit_courant['id_produit'] ?>">
        <?php endif; ?>
        <label for="salle">Choix de la salle</label>
        <select class="form-row form-control mb-2" name="id_salle">
            <?php
            foreach ($salles as $salle) {
            ?>
                <option <?php echo (
                            //($_POST["id_salle"]?? null) === $salle['id_salle']
                            (isset($_POST['id_salle']) && ($_POST['id_salle'] == $salle['id_salle'])) ||
                            (isset($produit_courant['id_salle']) && $produit_courant['id_salle'] == $salle['id_salle'])) ? 'selected' : '' ?> value="<?php echo $salle['id_salle'] ?>">
                    <?php echo ($salle['id_salle'] . ' - ' . $salle['titre'] . ' - ' . $salle['ville'] . ', ' . $salle['cp'] . ', ' . $salle['pays'] . ' - capacite : ' . $salle['capacite'] . 'personnes') ?>
                </option>
            <?php
            }

            ?>
        </select>
        <div class="form-row">
            <div class="d-flex flex-row date input-daterange col-md-6" id="datepicker">
                <div class="col-md-6 pl-0">
                    <label for="range-start">Date d'arrivée</label>
                    <div class="form-group">
                        <input type="text" name="range-start" class="form-control datepicker-input" value="<?php echo $dateArrivee ?? $_POST['range-start'] ?? '' ?>" required>
                    </div>
                </div>
                <div class="col-md-6 pr-0">
                    <label for="range-end">Date de départ</label>
                    <div class="form-group">
                        <input type="text" name="range-end" class="form-control datepicker-input" value="<?php echo $dateDepart ?? $_POST['range-start'] ?? '' ?>" required>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-row col-md-6" id="timepicker">
                <div class="col-md-6 pl-0">
                    <label for="rangeTime-start">Heure d'arrivée</label>
                    <div class="form-group">
                        <input type="text" name="rangeTime-start" class="form-control js-time-pickerStart" value="<?php echo $heureArrivee ?? $_POST['rangeTime-start'] ?? '08:00' ?>">
                    </div>
                </div>
                <div class="col-md-6 pr-0">
                    <label for="rangeTime-end">Heure de départ</label>
                    <div class="form-group">
                        <input type="text" name="rangeTime-end" class="form-control js-time-pickerEnd" value="<?php echo $heureDepart ?? $_POST['rangeTime-end'] ?? '08:00' ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 pl-0">
            <label for="prix">Prix</label>
            <div class="form-group">
                <input type="number" name="prix" class="form-control " value="<?php echo  $produit_courant['prix'] ?? $_POST['prix'] ?? '' ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>

    </form>

<?php

}

require_once('../inc/footer.php');
