<?php

function startSess() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function csrf() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}
?>