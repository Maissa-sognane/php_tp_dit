<?php
/*
 * Declarations des constants pour ma connexion
 **/
const DB_SERVER = 'localhost';
const DB_USERNAME = 'root';
const DB_PASSWORD = '';
const DB_NAME = 'php_tp';

/*Connexion a MYSQL */

$link = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_NAME);

//Verification

if($link === false){
    die("ERROR: could not connect. ".mysqli_connect_errno());
}

?>