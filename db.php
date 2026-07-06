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
    // Se a base de dados não existe, tentamos criá-la automaticamente
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        try {
            $tmp = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // Criar base de dados
            $tmp->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            // Selecionar DB e criar tabelas se não existirem
            $tmp->exec("USE `$DB_NAME`;
                CREATE TABLE IF NOT EXISTS users (
                  nrprocesso INT UNSIGNED NOT NULL,
                  name VARCHAR(150) NOT NULL,
                  password VARCHAR(255) NOT NULL,
                  role ENUM('funcionario','professor','administrador') NOT NULL,
                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (nrprocesso)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                CREATE TABLE IF NOT EXISTS products (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  name VARCHAR(255) NOT NULL,
                  quantity INT NOT NULL DEFAULT 0,
                  description TEXT,
                  returnable TINYINT(1) NOT NULL DEFAULT 0,
                  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                CREATE TABLE IF NOT EXISTS orders (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  product_id INT DEFAULT NULL,
                  product_name VARCHAR(255) NOT NULL,
                  quantity INT NOT NULL DEFAULT 1,
                  requester_id INT NOT NULL,
                  return_required TINYINT(1) NOT NULL DEFAULT 0,
                  status VARCHAR(40) NOT NULL DEFAULT 'pendente',
                  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                CREATE TABLE IF NOT EXISTS salas (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  nome VARCHAR(150) NOT NULL,
                  capacidade INT DEFAULT NULL,
                  localizacao VARCHAR(255) DEFAULT NULL,
                  created_by INT DEFAULT NULL,
                  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                CREATE TABLE IF NOT EXISTS horarios (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  sala_id INT NOT NULL,
                  dia_semana ENUM('segunda','terca','quarta','quinta','sexta','sabado','domingo') DEFAULT NULL,
                  data_especifica DATE DEFAULT NULL,
                  hora_inicio TIME NOT NULL,
                  hora_fim TIME NOT NULL,
                  disponivel TINYINT(1) NOT NULL DEFAULT 1,
                  created_by INT DEFAULT NULL,
                  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                  FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                CREATE TABLE IF NOT EXISTS room_requests (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  sala_id INT NOT NULL,
                  horario_id INT NOT NULL,
                  requester_id INT NOT NULL,
                  status ENUM('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente',
                  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                  FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE CASCADE,
                  FOREIGN KEY (horario_id) REFERENCES horarios(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            // Re-conectar à base de dados criada
            $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (Exception $e2) {
            die('Erro ao criar base de dados/tabelas: ' . $e2->getMessage());
        }
    } else {
        // Em ambiente de produção não mostrar detalhes
        die('Erro de ligação à base de dados: ' . $e->getMessage());
    }
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
} catch (PDOException $e) {
    // ignorar se alguma tabela ainda não existir durante o bootstrap
}
