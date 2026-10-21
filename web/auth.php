<?php
// Server-side guard for parent pages. Include as the first line of every protected page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['parent_id'], $_SESSION['role']) || $_SESSION['role'] !== 'parent') {
    header('Location: login.php');
    exit;
}
?>
