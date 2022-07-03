<?php
//Initialisation du session
session_start();

$_SESSION = array();

//Detruire le session

session_destroy();

//Redirection
header("location: ../index.php");
exit;

?>