<?php
/**
 * logout.php - destroys the session and returns to login.
 */
require_once 'includes/db_connect.php';

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

session_start();                       // fresh session just to carry the message
flash("You have been logged out.", "info");
header("Location: login.php");
exit;
