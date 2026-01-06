<?php
// db.php - conexão PDO
$DB_HOST = '127.0.0.1';
$DB_NAME = 'pap_app';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    // Em ambiente de produção não mostrar detalhes
    die('Erro de ligação à base de dados: ' . $e->getMessage());
}
