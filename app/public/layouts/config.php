<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
/*define('DB_SERVER', 'localhost:3306');*/
define('DB_SERVER', 'localhost:3306');
define('DB_USERNAME', 'eruotolo');
define('DB_PASSWORD', 'Guns026772');
define('DB_NAME', 'donbano');

/*-------- SERVIDOR DE PRODUCCIÓN --------*/
/*
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'cch94190_sistema');
define('DB_PASSWORD', 'Guns026772');
define('DB_NAME', 'cch94190_sistema');
 */

/*-------- SERVIDOR DE TESTING --------*/
/*
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u2g8v9gzzgram');
define('DB_PASSWORD', 'Guns026772');
define('DB_NAME', 'dbjnf0hv3xnfyr');*/


/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);


// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Set the character encoding to UTF-8
if (!mysqli_set_charset($link, "utf8")) {
    echo "Failed to set character encoding to UTF-8: " . mysqli_error($link);
    exit();
}


$gmailid = ''; // YOUR gmail email
$gmailpassword = ''; // YOUR gmail password
$gmailusername = ''; // YOUR gmail User name

?>