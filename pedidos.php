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

function formatDateValue($value) {
    if (empty($value)) {
        return '-';
    }

    try {
        $date = new DateTime($value);
        return $date->format('d/m/Y');
    } catch (Exception $e) {
        return htmlspecialchars((string)$value);
    }
}

$stmt = $pdo->prepare("SELECT o.id, o.nome_produto AS product_name, o.quantidade AS quantity, o.estado AS status, o.devolucao_obrigatoria AS return_required, u.numero_processo AS requester_nr, o.pedido_por AS requester_id
  FROM pedidos o
  LEFT JOIN utilizadores u ON u.numero_processo = o.pedido_por
  ORDER BY o.id DESC");
$stmt->execute();
$orders = $stmt->fetchAll();
// obter pedidos de sala pendentes
$stmt = $pdo->prepare('SELECT rr.id, rr.pedido_por AS requester_id, rr.estado AS status, u.numero_processo AS requester_nr, s.nome AS sala_nome, h.hora_inicio, h.hora_fim, h.data_especifica FROM requisicao_sala rr LEFT JOIN utilizadores u ON u.numero_processo = rr.pedido_por LEFT JOIN salas s ON s.id = rr.sala_id LEFT JOIN horarios h ON h.id = rr.horario_id WHERE rr.estado = "pendente" ORDER BY rr.id DESC');
$stmt->execute();
$room_requests = $stmt->fetchAll();
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
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Quantidade</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Devolução</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Nº Processo</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Ações</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Status</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo $o['id']; ?></div></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo htmlspecialchars($o['product_name']); ?></div></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2;"><div class="box"><?php echo (int)$o['quantity']; ?></div></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo $o['return_required'] ? 'Sim' : 'Não'; ?></div></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo htmlspecialchars($o['requester_nr'] ?? $o['requester_id']); ?></div></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2">
              <div class="action-cell">
                <?php if ($o['status'] === 'pendente'): ?>
                  <form method="post" action="acao_pedidos.php" style="display:flex;gap:6px;align-items:center">
                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                    <select name="action" class="action-select">
                      <option value="approve">Aprovar</option>
                      <option value="reject">Rejeitar</option>
                    </select>
                    <button class="action-btn action-submit" type="submit">OK</button>
                  </form>
                <?php elseif ($o['status'] === 'aprovado' && $o['return_required']): ?>
                  <div>Esperar pedido de devolução</div>
                <?php else: ?>
                  —
                <?php endif; ?>
              </div>
            </td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo formatOrderStatus($o['status']); ?></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

      <h2 style="margin-top:24px">Pedidos de Salas</h2>
      <?php if (count($room_requests) === 0): ?>
        <p>Não existem pedidos de sala pendentes.</p>
      <?php else: ?>
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr>
              <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">ID</th>
              <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Sala</th>
              <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Horário</th>
              <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Nº Processo</th>
              <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Ações</th>
              <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Status</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($room_requests as $r): ?>
            <tr>
              <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo $r['id']; ?></div></td>
              <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo htmlspecialchars($r['sala_nome']); ?></div></td>
              <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo htmlspecialchars(formatDateValue($r['data_especifica'])) . ' ' . htmlspecialchars($r['hora_inicio']) . '-' . htmlspecialchars($r['hora_fim']); ?></div></td>
              <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo htmlspecialchars($r['requester_nr'] ?? $r['requester_id']); ?></div></td>
              <td style="padding:8px;border-bottom:1px solid #f2f2f2">
                <div class="action-cell">
                  <form method="post" action="acao_pedidos.php" style="display:flex;gap:6px;align-items:center">
                    <input type="hidden" name="type" value="room">
                    <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                    <select name="action" class="action-select">
                      <option value="approve">Aprovar</option>
                      <option value="reject">Rejeitar</option>
                    </select>
                    <button class="action-btn action-submit" type="submit">OK</button>
                  </form>
                </div>
              </td>
              <td style="padding:8px;border-bottom:1px solid #f2f2f2"><div class="box"><?php echo htmlspecialchars($r['status']); ?></div></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

    <p style="margin-top:18px"><a href="funcionario.php">Voltar</a></p>
  </main>
</body>
</html>