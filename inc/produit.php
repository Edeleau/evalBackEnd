<?php
require_once('init.php');
$whereclause = '';
$args = [];
if (!empty($_GET) && isset($_GET)) {
    foreach ($_GET as $key => $value) {
        if ($key === 'order' || $key === 'limit') {
            $whereclause .= ' ';
        } elseif (!empty($whereclause)) {
            $whereclause .= 'AND ';
        } else {
            $whereclause =  'WHERE ';
        }
        switch ($key) {
            case 'capacite':
                $whereclause .= $key . '>=:' . $key . ' ';
                break;
            case 'prix':
                $whereclause .= $key . '<=:' . $key . ' ';
                break;
            case 'date_arrivee':
                $whereclause .= sprintf('DATE(:%s) >= DATE(p.date_arrivee) ', $key);
                break;
            case 'date_depart':
                $whereclause .= sprintf('DATE(:%s) <= DATE(p.date_depart) ', $key);
                break;
            case 'order':
                if ($value === ('prix') || $value === ('capacite')) {
                    $whereclause .= sprintf('ORDER BY %s ', $value);
                } else {
                    $whereclause .= 'ORDER BY p.id_produit';
                }
                break;
            case 'limit':
                if (is_numeric($value) && $value > 0 ) {
                    $whereclause .= sprintf('limit %s', $value);
                }else{
                    $whereclause .='limit 10 ';
                 }
                 break;
            default:
                $whereclause .= $key . '=:' . $key . ' ';
                break;
        }
        if ($key !== 'order' && $key !== 'limit') {
            $args[$key] = $value;
        }
    }
}else{
    $whereclause .=' limit 10 ';
}

$produits = execRequete("SELECT *  FROM produit p
INNER JOIN salle s 
ON s.id_salle = p.id_salle
$whereclause", $args);
if ($produits->rowCount() == 0) {
?>
    <div class="alert alert-info mt-5 ">Pas de produit dans la boutique revenez bientot</div>
<?php
} else {
?>
    <div class="row">
        <?php
        while ($produit = $produits->fetch()) :
            $produit['date_arrivee'] = (new DateTime($produit['date_arrivee']))->format('d/m/Y');
            $produit['date_depart'] = (new DateTime($produit['date_depart']))->format('d/m/Y');
        ?>
            <div class="col-md-6 my-3">
                <div class="border border-dark pb-2">
                    <div class="thumbnail">
                        <a href="fiche.php?id_produit=<?php echo $produit['id_produit'] ?>">
                            <img src="<?php echo URL . 'photos/' . $produit['photo'] ?>" alt="<?php echo $produit['titre'] ?>" class="img-fluid">
                        </a>
                    </div>
                    <div class="caption pt-2 px-2">
                        <h4 class="float-right">
                            <?php echo number_format($produit['prix'], 2, ',', '&nbsp;') ?>€
                        </h4>
                        <a href="fiche.php?id_produit=<?php echo $produit['id_produit'] ?>">
                            <h4><?php echo $produit['titre'] ?></h4>
                        </a>
                        <p><?php echo (iconv_strlen($produit['description']) > 30) ? substr($produit['description'], 0, 30) . '...'  : $produit['description']; ?></p>
                        <p><i class="fas fa-users"></i> <?php echo $produit['capacite'] ?></p>
                        <p><i class="fas fa-calendar-alt"></i> Date arrivée : <?php echo $produit['date_arrivee'] ?></p>
                        <p><i class="fas fa-calendar-alt"></i> Date départ : <?php echo $produit['date_depart'] ?></p>
                    </div>
                </div>
            </div>

        <?php
        endwhile;
        ?>
    </div>
    
<?php
}
?>