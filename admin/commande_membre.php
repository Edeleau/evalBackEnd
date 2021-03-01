<?php

require_once('../inc/init.php');
$title = 'Commande du membre : ' . $_GET['id_membre'];

$id_membre = $_GET['id_membre'];
$profil = execRequete("SELECT * FROM membre WHERE id_membre = :id_membre", [
    'id_membre' => $id_membre
])->fetch();
if (!isAdmin() && empty($id_membre)) {
    header('location:' . URL . 'index.php');
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
    'id_membre' => $_GET['id_membre']
]);
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_commande']) && !empty($_GET['id_membre']) && !empty($_GET['id_produit']) && isAdmin()) {
    execRequete("UPDATE produit SET 
    etat       = 'libre'
    WHERE id_produit = :id_produit ", [
        'id_produit' => $_GET['id_produit']
    ]);
    execRequete("DELETE FROM commande WHERE id_commande=:id_commande", [
        'id_commande' => $_GET['id_commande']
    ]);


    header("location:" . URL . 'admin/commande_membre.php?action=view&&id_membre=' . $_GET['id_membre']);
    exit;
}
require_once('../inc/header.php');
?>
<h1>Commandes du membre : <?php echo $_GET['id_membre'] ?></h1>
<hr>
<table class="table table-bordered table-striped table-responsive-xl mt-3">
    <tr>
        <th>ID commande</th>
        <th>Membre</th>
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
            <td><?php echo $commande['id_membre'] . '-' . $commande['prenom'] . ' ' . $commande['nom'] ?></td>
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
require_once('../inc/footer.php');
