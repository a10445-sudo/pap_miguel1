<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?msg=' . urlencode('Por favor entre para aceder.'));
    exit;
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'funcionario') {
    header('Location: dashboard.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';
// criar tabela se não existir
$pdo->exec("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$msg = $_GET['msg'] ?? '';
$stmt = $pdo->query('SELECT * FROM products ORDER BY id DESC');
$products = $stmt->fetchAll();
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inventário</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Inventário</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if (count($products) === 0): ?>
      <p>O inventário está vazio. <a href="registar_produto.php">Registe um produto</a>.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">ID</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Produto</th>
            <th style="text-align:right;padding:8px;border-bottom:1px solid #eee">Quantidade</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Descrição</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo $p['id']; ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($p['name']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2;text-align:right"><?php echo (int)$p['quantity']; ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($p['description']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <p style="margin-top:18px"><a href="registar_produto.php">Registar novo produto</a> · <a href="funcionario.php">Voltar</a></p>
  </main>
</body>
</html>