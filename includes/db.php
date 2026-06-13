<?php

$host = "localhost";
$dbname = "u920728779_FvskT";
$username = "u920728779_AZUz7";
$password = "Diff@12345@";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e){

    die("Database Connection Failed: " . $e->getMessage());
}

function db(){

    global $pdo;

    return $pdo;
}

function dbOne($query, $params = []){

    $stmt = db()->prepare($query);

    $stmt->execute($params);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function dbAll($query, $params = []){

    $stmt = db()->prepare($query);

    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function dbInsert($query, $params = []){

    $stmt = db()->prepare($query);

    return $stmt->execute($params);
}

function setting($key, $default = ''){

    $stmt = db()->prepare(
        "SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1"
    );

    $stmt->execute([$key]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row['setting_value'] ?? $default;
}
?>