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

function formatOrderStatus($status) {
    $labels = [
        'pendente' => 'Pendente',
        'aprovado' => 'Aprovado',
        'rejeitado' => 'Rejeitado',
        'devolucao_pendente' => 'Devolução pendente',
        'devolvido' => 'Devolvido',
    ];
    return $labels[$status] ?? htmlspecialchars($status);
}

$stmt = $pdo->prepare("SELECT o.id, o.nome_produto AS product_name, o.quantidade AS quantity, o.estado AS status, o.devolucao_obrigatoria AS return_required, u.numero_processo AS requester_nr, o.pedido_por AS requester_id
  FROM pedidos o
  LEFT JOIN utilizadores u ON u.numero_processo = o.pedido_por
  WHERE o.estado = 'devolucao_pendente' AND o.devolucao_obrigatoria = 1
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
  <title>Devoluções - Funcionário</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container container-wide">
    <h1>Devoluções</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if (count($orders) === 0): ?>
      <p>Não existem produtos pendentes de devolução.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">ID</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Produto</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Quantidade</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Solicitante</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Status</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Ação</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo $o['id']; ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($o['product_name']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo (int)$o['quantity']; ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($o['requester_nr'] ?? $o['requester_id']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo formatOrderStatus($o['status']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2">
              <?php if ($o['status'] === 'devolucao_pendente'): ?>
                <form method="post" action="acao_pedidos.php" style="display:inline">
                  <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                  <input type="hidden" name="action" value="return">
                  <button type="submit" class="action-btn action-submit">Marcar Devolvido</button>
                </form>
              <?php else: ?>
                Aguarda devolução do professor
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <p style="margin-top:18px"><a href="funcionario.php">Voltar</a></p>
  </main>
</body>
</html>
