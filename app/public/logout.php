<?php
// Initialize the session
session_start();

require_once "layouts/config.php";
require_once "layouts/activity_logger.php";

// Log the logout BEFORE unsetting $_SESSION (ctx reads id/username from it).
log_activity_ctx($link, 'LOGOUT');

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header("location: auth-login.php");
exit;
?>