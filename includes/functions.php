<?php
function sanitize($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($url)
{
    header("Location: " . $url);
    exit();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function checkAuth()
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function checkAdmin()
{
    if (!isAdmin()) {
        redirect('../index.php');
    }
}
