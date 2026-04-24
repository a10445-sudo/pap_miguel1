<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?msg=' . urlencode('Por favor entre para aceder.'));
    exit;
}
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'funcionario' && $_SESSION['user_role'] !== 'administrador')) {
    header('Location: dashboard.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $quantity = $_POST['quantity'] ?? '';
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || !is_numeric($quantity)) {
        $msg = 'Preencha o nome e quantidade válida.';
    } else {
        // criar tabela se não existir
        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL DEFAULT 0,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $stmt = $pdo->prepare('INSERT INTO products (name, quantity, description) VALUES (?, ?, ?)');
        $stmt->execute([$name, (int)$quantity, $description]);
        header('Location: inventario.php?msg=' . urlencode('Produto registado com sucesso.'));
        exit;
    }
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registar Produto</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Registar Produto</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <form method="post">
      <label for="name">Nome do produto</label>
      <input type="text" id="name" name="name" required>

      <label for="quantity">Quantidade</label>
      <input type="number" id="quantity" name="quantity" value="1" min="0" required>

      <label for="description">Descrição (opcional)</label>
      <input type="text" id="description" name="description">

      <button type="submit">Registar</button>
    </form>

    <p style="margin-top:18px"><a href="inventario.php">Ver inventário</a> · <a href="funcionario.php">Voltar</a></p>
  </main>
</body>
</html>