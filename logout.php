<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_unset();
session_destroy();

// Clear cookie
if (isset($_COOKIE['user_email'])) {
    setcookie('user_email', '', time() - 3600, "/");
}

redirect('login.php');
?>
