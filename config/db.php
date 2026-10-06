<?php
declare(strict_types=1);
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'smart_parish';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
} catch (PDOException $e) {
    error_log($e->getMessage()); http_response_code(500); exit('Database connection failed.');
}
