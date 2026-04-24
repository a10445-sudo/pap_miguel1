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
// criar tabela de pedidos (simples) se não existir
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    requester_id INT NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'pendente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$stmt = $pdo->prepare("SELECT o.*, u.nrprocesso AS requester_nr
  FROM orders o
  LEFT JOIN users u ON u.nrprocesso = o.requester_id
  WHERE o.status = 'pendente'
  ORDER BY o.id DESC");
$stmt->execute();
$orders = $stmt->fetchAll();
$msg = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pedidos</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container container-wide">
    <h1>Pedidos</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if (count($orders) === 0): ?>
      <p>Não existem pedidos registados.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">ID</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Produto</th>
            <th style="text-align:right;padding:8px;border-bottom:1px solid #eee">Quantidade</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Nº Processo (Requerente)</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Ações</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Status</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo $o['id']; ?></div></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo htmlspecialchars($o['product_name']); ?></div></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2;text-align:right"><div class="box"><?php echo (int)$o['quantity']; ?></div></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo htmlspecialchars($o['requester_nr'] ?? $o['requester_id']); ?></div></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2">
              <form method="post" action="order_action.php" style="display:flex;gap:6px;align-items:center">
                <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                <select name="action" class="action-select">
                  <option value="approve">Aprovar</option>
                  <option value="reject">Rejeitar</option>
                </select>
                <button class="action-btn action-submit" type="submit">OK</button>
              </form>
            </td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo htmlspecialchars($o['status']); ?></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <p style="margin-top:18px"><a href="funcionario.php">Voltar</a></p>
  </main>
</body>
</html>