<?php

require_once __DIR__.'/auth.php';

startSess();

if(!isset($_SESSION['admin'])){

    header("Location: /admin-login.php");
    exit;
}
?>