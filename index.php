<?php
require_once('inc/init.php');

$title = 'Accueil';

require_once('inc/header.php');
//Corps de la page
$categories = execRequete("SELECT DISTINCT categorie FROM produit p
INNER JOIN salle s 
ON s.id_salle = p.id_salle
ORDER BY categorie");
$villes = execRequete("SELECT DISTINCT ville FROM produit p
INNER JOIN salle s 
ON s.id_salle = p.id_salle
ORDER BY ville");
$info = execRequete("SELECT MAX(prix) AS prix_max , MIN(prix) AS prix_min , MAX(capacite) AS capacite_max, MIN(capacite) AS capacite_min FROM produit p
INNER JOIN salle s 
ON s.id_salle = p.id_salle")->fetch();




?>
<div class="row">
    <div class="col-md-3" id="ajax">
        <p class="lead pt-3">Catégories</p>
        <div class="list-group">
            <a href="?categorie=" class="list-group-item categorie">Toutes</a>
            <?php
            while ($categorie = $categories->fetch()) {
            ?>
                <a href="?categorie=<?php echo $categorie['categorie'] ?>" class="list-group-item categorie">
                    <?php echo $categorie['categorie']; ?>
                </a>
            <?php
            }
            ?>
        </div>
        <p class="lead pt-3">Ville</p>

        <div class="form-group">
            <select class="form-control" id="ville" name="ville">
                <option value="">Toutes les villes</option>
                <?php
                while ($ville = $villes->fetch()) {
                ?>
                    <option value="<?php echo $ville['ville'] ?>"><?php echo ucfirst($ville['ville']) ?></option>
                <?php
                }
                ?>
            </select>
        </div>

        <p class="lead pt-3">Capacité minimale : </p>

        <div class="form-group">
            <select class="form-control" id="capacite" name="capacite">
                <option value="">Toutes les capacité</option>
                <?php
                for ($i = $info['capacite_min']; $i <= $info['capacite_max']; $i += 10) {
                ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php
                }
                ?>
            </select>
        </div>
        <p class="lead pt-3">Trier par : </p>
        <div class=" form-group">
            <select class="form-control ml-0" id="order" name="order">
                <option value="">Aucun tri</option>
                <option value="capacite">Capacite</option>
                <option value="prix">Prix</option>
            </select>
        </div>
        <p>Prix max:</p>
        <input type="range" class="w-100 range" value="<?php echo $info['prix_max'] ?>" id="prix" name="prix" min="<?php echo $info['prix_min'] ?>" max="<?php echo $info['prix_max'] ?>" step="100">
        <p id="affichePrix"></p>
        <div class="form-row" id="datepicker">
            <div>
                <label for="date_arrivee">Date d'arrivée</label>
                <div class="form-group">
                    <input type="text" name="date_arrivee" id="date_arrivee" class="form-control datepicker-input" value="<?php echo $dateArrivee ?? $_POST['range-start'] ?? '' ?>" required>
                </div>
            </div>
            <div>
                <label for="date_depart">Date de départ</label>
                <div class="form-group">
                    <input type="text" name="date_depart" id="date_depart" class="form-control datepicker-input" value="<?php echo $dateDepart ?? $_POST['range-start'] ?? '' ?>" required>
                </div>
            </div>
        </div>
        <p>Nombre de résultats max : </p>
        <div class=" form-group">
            <select class="form-control ml-0" id="limit" name="limit">
                <option value=""></option>
                <option value="10">10</option>
                <?php
                for ($i = 50; $i <= 200; $i += 50) {
                ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>

    <div class="col-md-9" id="resultat">
        <?php
        require_once('inc/produit.php');
        ?>
    </div>
</div>


<?php
require_once('inc/footer.php');
