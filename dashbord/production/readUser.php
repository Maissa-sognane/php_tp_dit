<?php
// On détermine sur quelle page on se trouve
if(isset($_GET['page']) && !empty($_GET['page'])){
    $currentPage = (int) strip_tags($_GET['page']);
}else{
    $currentPage = 1;
}
// On se connecte à là base de données
try{
    // Connexion à la base
    $link = new PDO('mysql:host=localhost;dbname=php_tp', 'root', '');
    $link->exec('SET NAMES "UTF8"');
} catch (PDOException $e){
    echo 'Erreur : '. $e->getMessage();
    die();
}


// On détermine le nombre total d'articles
$sql = 'SELECT COUNT(*) AS nb_users FROM `users`;';

// On prépare la requête
$query = $link->prepare($sql);

// On exécute
$query->execute();

// On récupère le nombre d'articles
$result = $query->fetch();

$nbUsers = (int) $result['nb_users'];

// On détermine le nombre d'articles par page
$parPage = 5;

// On calcule le nombre de pages total
$pages = ceil($nbUsers / $parPage);

// Calcul du 1er article de la page
$premier = ($currentPage * $parPage) - $parPage;

$sql = 'SELECT * FROM `users` ORDER BY `created_at` DESC LIMIT :premier, :parpage;';

// On prépare la requête
$query = $link->prepare($sql);

$query->bindValue(':premier', $premier, PDO::PARAM_INT);
$query->bindValue(':parpage', $parPage, PDO::PARAM_INT);

// On exécute
$query->execute();

// On récupère les valeurs dans un tableau associatif
$users = $query->fetchAll(PDO::FETCH_ASSOC);
