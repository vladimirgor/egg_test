<?php
function dbConnection()
{
    include_once(__DIR__ . '/config.php');
    $host = DB_HOST;
    $db = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;
    $dsn = "mysql:host=$host; dbname=$db";
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];
//db connection
    try {
        $pdo = new PDO($dsn, $user, $pass, $opt);
        return $pdo;
    } catch (PDOException $e) {
//log
        echo 'Something went wrong. Please, apply to system administrator.';
        error_log( date('Y-m-d H:i:s') . ' - Connection error: ' . $e->getMessage()
            . PHP_EOL,3,__DIR__ . '/message_log');
        die;
    }
}
