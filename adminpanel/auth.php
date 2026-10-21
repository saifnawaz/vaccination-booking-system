<?php
// Server-side guard for admin panel pages. Include as the first line of every protected page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'], $_SESSION['userrole'], $_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Allow only the given roles, e.g. require_role('admin') or require_role('admin', 'hospital').
function require_role(...$roles)
{
    if (!in_array($_SESSION['userrole'], $roles, true)) {
        header('Location: index.php');
        exit;
    }
}
?>
