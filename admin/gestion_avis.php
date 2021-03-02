<?php

require_once('../inc/init.php');
$title = 'Gestion des avis';

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}
$order = '';
$testOrder = ['id_avis', 'm.id_membre', 's.id_salle',  'commentaire', 'note', 'a.date_enregistrement'];
if (isset($_GET['order']) &&  in_array($_GET['order'], $testOrder)) {
    $order = 'ORDER BY ' . $_GET['order'];
}
$avisS = execRequete('SELECT *, a.date_enregistrement FROM avis a
INNER JOIN membre m 
ON m.id_membre = a.id_membre
INNER JOIN salle s
ON a.id_salle = s.id_salle '. $order);

if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_avis']) && isAdmin()) {
    execRequete("DELETE FROM avis WHERE id_avis=:id_avis", [
        'id_avis' => $_GET['id_avis']
    ]);
    header("location:" . URL . 'admin/gestion_avis.php');
    exit;
}
require_once('../inc/header.php');
?>
<h1>Gestion des avis</h1>
<hr>
<table class="table table-bordered table-striped table-responsive-xl mt-3">
    <tr>
        <th><a href="?order=id_avis">ID avis</a></th>
        <th><a href="?order=m.id_membre">Membre</a></th>
        <th><a href="?order=s.id_salle">Salle</a></th>
        <th><a href="?order=commentaire">Commentaire</a></th>
        <th><a href="?order=note">Note</a></th>
        <th><a href="?order=a.date_enregistrement">Date de l'avis</a></th>
        <th>Action</th>

    </tr>
    <?php
    while ($avis = $avisS->fetch()) :
    ?>
        <tr>
            <td><?php echo $avis['id_avis'] ?></td>
            <td><?php echo $avis['id_membre'].'-'.$avis['prenom'].' '.$avis['nom'] ?></td>
            <td><?php echo $avis['id_salle'].'-'.$avis['titre'] ?></td>
            <td><?php echo $avis['commentaire'] ?></td>
            <td><?php
                for ($i=0; $i < $avis['note'] ; $i++) { 
                    echo '☆';
                }
            ?></td>
            <td><?php echo $avis['date_enregistrement'] ?></td>
            <td class="row d-flex justify-content-around m-0 h-100  border-right-0 border-left-0 border-bottom-0">
                <a href="?id_avis=<?php echo $avis['id_avis']?>&&action=delete" class="confirm"><i class="far fa-trash-alt"></i></a>
            </td>
        </tr>
    <?php
    endwhile;
    ?>
</table>
<?php
require_once('../inc/footer.php');
