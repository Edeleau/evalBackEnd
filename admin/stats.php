<?php

require_once('../inc/init.php');
$title = 'Stats';

$top = [
    'note' => 'SELECT ROUND(AVG(note),2) AS note, titre , a.id_salle FROM avis a 
               INNER JOIN salle s 
               ON a.id_salle = s.id_salle 
               GROUP BY titre
               ORDER BY ROUND(AVG(note),2) DESC LIMIT 5',
    'commande' => 'SELECT COUNT(p.id_salle) AS nb_commande, p.id_salle , titre FROM commande c 
                   INNER JOIN produit p ON p.id_produit = c.id_produit 
                   INNER JOIN salle s ON s.id_salle = p.id_salle 
                   GROUP BY p.id_salle 
                   ORDER BY COUNT(p.id_salle) DESC LIMIT 5',
    'quantite_commande' => 'SELECT COUNT(c.id_commande) AS nb_commande, c.id_membre, pseudo FROM commande c 
                            INNER JOIN membre m ON m.id_membre = c.id_membre 
                            WHERE m.id_membre = c.id_membre
                            GROUP BY m.id_membre
                            ORDER BY COUNT(id_commande) DESC LIMIT 5',
    'prix' => 'SELECT SUM(prix) AS prix, c.id_membre , pseudo FROM commande c 
               INNER JOIN membre m ON m.id_membre = c.id_membre 
               INNER JOIN produit p ON p.id_produit = c.id_produit 
               WHERE m.id_membre = c.id_membre
               GROUP BY m.id_membre
               ORDER BY SUM(prix) DESC LIMIT 5',
];
$test = false;
if (isset($_GET['top'])) {
    foreach ($top as $key => $value) {
        if ($key === $_GET['top']) {
            $test = true;
        }
    }
}
if (!$test) {
    $_GET['top'] = 'note';
}


$resultats = execRequete($top[$_GET['top']]);
if (!isAdmin()) {
    header('location:' . URL . 'index.php');
    exit();
}

require_once('../inc/header.php');
?>
<h1>Stats du site</h1>
<hr>
<div class="row justify-content-center">
    <a href="?top=note" class=" m-2 btn btn-primary">Top 5 note</a>
    <a href="?top=commande" class=" m-2 btn btn-primary">Top 5 des salles les plus commandé</a>
    <a href="?top=quantite_commande" class=" m-2 btn btn-primary">Top 5 des membre qui commandent le plus</a>
    <a href="?top=prix" class=" m-2 btn btn-primary">Top 5 des membre qui ont le plus payé</a>
</div>


<table class="table table-bordered table-striped table-responsive-xl mt-3">
    <?php
    $i = 1;
    while ($resultat = $resultats->fetch()) {
    ?>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-2"><?php echo $i ?></div>
                    <div class="col">
                        <?php
                        switch ($_GET['top']) {
                            case 'note':
                                echo $resultat['titre'] . ' avec une note de ' . $resultat['note'];
                                break;
                            case 'commande':
                                echo $resultat['titre'] . ' avec une total de ' . $resultat['nb_commande']. ' commande(s).' ;
                                break;
                            case 'quantite_commande':
                                echo $resultat['pseudo'] . ' avec une total de ' . $resultat['nb_commande']. ' commande(s).' ;
                                break;
                            case 'prix':
                                echo $resultat['pseudo'] . ' avec une total de ' . $resultat['prix']. ' &euro; dépensé.' ;
                                break;
                            default:
                                # code...
                                break;
                        }
                        ?></div>
                </div>
            </div>
        </div>
    <?php
        $i++;
    }
    ?>
</table>
<?php
require_once('../inc/footer.php');
