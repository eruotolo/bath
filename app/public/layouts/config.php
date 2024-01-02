<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
/*define('DB_SERVER', 'localhost:3306');*/
define('DB_SERVER', 'localhost:3306');
define('DB_USERNAME', 'eruotolo');
define('DB_PASSWORD', 'Guns026772');
define('DB_NAME', 'donbano');


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