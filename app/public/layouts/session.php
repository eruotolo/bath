<?php
// Initialize the session
session_start();

        //print_r($_SESSION);
        //die();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: auth-login.php");
    exit;
}
?>