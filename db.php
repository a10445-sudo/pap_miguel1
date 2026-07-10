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
} catch (PDOException $e) {
    die('Erro de ligação à base de dados: ' . $e->getMessage());
}

// Garantir colunas adicionais quando o banco de dados já existe em versões antigas
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'returnable'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN returnable TINYINT(1) NOT NULL DEFAULT 0");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'product_id'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN product_id INT DEFAULT NULL");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'return_required'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN return_required TINYINT(1) NOT NULL DEFAULT 0");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM horarios LIKE 'dia_semana'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("UPDATE horarios SET dia_semana = NULL WHERE dia_semana IN ('sabado','domingo')");
        $pdo->exec("ALTER TABLE horarios MODIFY dia_semana ENUM('segunda','terca','quarta','quinta','sexta') DEFAULT NULL");
    }
} catch (PDOException $e) {
    // ignorar se alguma tabela ainda não existir durante o bootstrap
}
