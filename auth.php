<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// User must be logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

/*Check User Role*/
function requireRole($allowed_roles)
{
    if (!isset($_SESSION["role"])) {
        header("Location: login.php");
        exit();
    }

    if (!in_array($_SESSION["role"], $allowed_roles)) {
        header("Location: dashboard.php?error=access_denied");
        exit();
    }
}
?>