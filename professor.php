<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?msg=' . urlencode('Por favor entre para aceder.'));
    exit;
}
if (!isset($_SESSION['user_role']) || trim($_SESSION['user_role']) !== 'professor') {
    header('Location: dashboard.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';

// criar tabela de pedidos se não existir
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    requester_id INT NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'pendente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Handle request submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    if ($product_name && $quantity > 0) {
        $stmt = $pdo->prepare('INSERT INTO orders (product_name, quantity, requester_id) VALUES (?, ?, ?)');
        $stmt->execute([$product_name, $quantity, $_SESSION['user_id']]);
        $msg = 'Pedido registado com sucesso.';
    } else {
        $msg = 'Dados inválidos.';
    }
}

// Get products
$stmt = $pdo->query('SELECT * FROM products ORDER BY name');
$products = $stmt->fetchAll();

// Get user's orders
$stmt = $pdo->prepare('SELECT * FROM orders WHERE requester_id = ? ORDER BY created_at DESC');
$stmt->execute([(int)$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$name = htmlspecialchars($_SESSION['user_name']);
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Painel do Professor - PAP</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Bem-vindo, <?php echo $name; ?>!</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <h2>Inventário</h2>
    <?php if (count($products) === 0): ?>
      <p>O inventário está vazio.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Produto</th>
            <th style="text-align:right;padding:8px;border-bottom:1px solid #eee">Quantidade Disponível</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Descrição</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($p['name']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2;text-align:right"><?php echo (int)$p['quantity']; ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($p['description']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <h2>Requisitar Produto</h2>
    <form method="post">
      <label for="product_name">Produto:</label>
      <select name="product_name" id="product_name" required>
        <option value="">Selecionar produto</option>
        <?php foreach ($products as $p): ?>
          <option value="<?php echo htmlspecialchars($p['name']); ?>"><?php echo htmlspecialchars($p['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <label for="quantity">Quantidade:</label>
      <input type="number" name="quantity" id="quantity" min="1" required>
      <button type="submit">Requisitar</button>
    </form>

    <h2>Estado dos Meus Pedidos</h2>
    <?php if (count($orders) === 0): ?>
      <p>Não tem pedidos registados.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Produto</th>
            <th style="text-align:right;padding:8px;border-bottom:1px solid #eee">Quantidade</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Status</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Data</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($o['product_name']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2;text-align:right"><?php echo (int)$o['quantity']; ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($o['status']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($o['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <p style="margin-top:18px"><a href="dashboard.php">Voltar ao Dashboard</a> · <a href="logout.php">Terminar sessão</a></p>
  </main>
</body>
</html>