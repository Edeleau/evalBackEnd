<?php
// Définition du fuseau horaire 
date_default_timezone_set('Europe/Paris');

// Ouverture de session
session_start();

//Connexion a la BDD
try {
$pdo = new PDO('mysql:host=localhost;charset=utf8;dbname=room', 'root', '', 

				array( PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING,
                        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
                ) 
			);
}catch(PDOException $e){
    echo $e->getMessage().'<br>'.$e->getFile().'<br>'.$e->getLine().'<br>';
    die("site indisponible. Contactez l'administrateur");
}
// Constante de site 

define('URL' , '/Ifocop/TP/Eval2/');

// Inclusion du fichier de functions
require_once('functions.php');
