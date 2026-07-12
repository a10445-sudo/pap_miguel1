<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?msg=' . urlencode('Por favor entre para aceder.'));
    exit;
}
if (!isset($_SESSION['user_role']) || strtolower(trim($_SESSION['user_role'])) !== 'professor') {
    header('Location: dashboard.php?msg=' . urlencode('Acesso negado. Role: ' . $_SESSION['user_role']));
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

// Handle request submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    
    if ($product_id > 0 && $quantity > 0) {
        $stmt = $pdo->prepare('SELECT id, nome AS name, quantidade AS quantity, descricao AS description, devolvivel AS returnable FROM produtos WHERE id = ?');
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            $msg = 'Produto não encontrado.';
        } elseif ($quantity > $product['quantity']) {
            $msg = 'Quantidade insuficiente em inventário.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO pedidos (produto_id, nome_produto, quantidade, pedido_por, devolucao_obrigatoria) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$product_id, $product['name'], $quantity, $_SESSION['user_id'], $product['returnable'] ? 1 : 0]);
            $msg = 'Pedido registado com sucesso.';
        }
    } else {
        $msg = 'Dados inválidos.';
    }
}

// Get products
$stmt = $pdo->query('SELECT id, nome AS name, quantidade AS quantity, descricao AS description, devolvivel AS returnable FROM produtos ORDER BY nome');
$products = $stmt->fetchAll();

// Load available horarios for room requests
$stmt = $pdo->query('SELECT h.id AS horario_id, h.hora_inicio, h.hora_fim, h.data_especifica, s.nome AS sala_nome FROM horarios h JOIN salas s ON s.id = h.sala_id WHERE h.disponivel = 1 ORDER BY s.nome, h.hora_inicio');
$available_horarios = $stmt->fetchAll();

// Get user's orders
$stmt = $pdo->prepare('SELECT id, produto_id AS product_id, nome_produto AS product_name, quantidade AS quantity, pedido_por AS requester_id, estado AS status, devolucao_obrigatoria AS return_required, criado_em AS created_at FROM pedidos WHERE pedido_por = ? ORDER BY criado_em DESC');
$stmt->execute([(int)$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Get user's room requests
$stmt = $pdo->prepare('SELECT rr.id, rr.sala_id, rr.horario_id, rr.pedido_por AS requester_id, rr.estado AS status, rr.criado_em AS created_at, s.nome AS sala_nome, h.hora_inicio, h.hora_fim, h.data_especifica FROM requisicao_sala rr JOIN salas s ON s.id = rr.sala_id JOIN horarios h ON h.id = rr.horario_id WHERE rr.pedido_por = ? ORDER BY rr.criado_em DESC');
$stmt->execute([(int)$_SESSION['user_id']]);
$room_requests = $stmt->fetchAll();

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
      <label for="product_id">Produto:</label>
      <select name="product_id" id="product_id" required>
        <option value="">Selecionar produto</option>
        <?php foreach ($products as $p): ?>
          <?php if ($p['quantity'] > 0): ?>
            <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (<?php echo (int)$p['quantity']; ?> disponível)</option>
          <?php endif; ?>
        <?php endforeach; ?>
      </select>
      <label for="quantity">Quantidade:</label>
      <input type="number" name="quantity" id="quantity" min="1" required>
      <button type="submit">Requisitar</button>
    </form>

    <h2>Solicitar Sala</h2>
    <?php if (count($available_horarios) === 0): ?>
      <p>Não existem horários disponíveis.</p>
    <?php else: ?>
      <form method="post" action="requisicao_sala.php">
        <label for="horario_id">Horário disponível:</label>
        <select name="horario_id" id="horario_id" required>
          <option value="">Selecionar</option>
          <?php foreach ($available_horarios as $ah): ?>
            <option value="<?php echo $ah['horario_id']; ?>"><?php echo htmlspecialchars($ah['sala_nome']) . ' — ' . ($ah['data_especifica'] ? $ah['data_especifica'] : 'Sem data') . ' ' . $ah['hora_inicio'] . '-' . $ah['hora_fim']; ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Pedir Sala</button>
      </form>
    <?php endif; ?>

    <h2>Estado dos Meus Pedidos</h2>
    <?php if (count($orders) === 0): ?>
      <p>Não tem pedidos registados.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Produto</th>
            <th style="text-align:right;padding:8px;border-bottom:1px solid #eee">Quantidade</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Devolução</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Status</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Ação</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Data</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($o['product_name']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2;text-align:right"><?php echo (int)$o['quantity']; ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo $o['return_required'] ? 'Sim' : 'Não'; ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo formatOrderStatus($o['status']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2">
              <?php if ($o['status'] === 'aprovado' && $o['return_required']): ?>
                <form method="post" action="acao_pedidos.php" style="display:inline">
                  <input type="hidden" name="type" value="product">
                  <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                  <input type="hidden" name="action" value="return_request">
                  <button type="submit" class="action-btn action-submit">Solicitar Devolução</button>
                </form>
              <?php elseif ($o['status'] === 'devolucao_pendente'): ?>
                Devolução solicitada
              <?php elseif ($o['status'] === 'devolvido'): ?>
                Devolvido
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($o['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <h2>Estado dos Meus Pedidos de Sala</h2>
    <?php if (count($room_requests) === 0): ?>
      <p>Não tem pedidos de sala registados.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Sala</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Horário</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Status</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Data</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($room_requests as $rr): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($rr['sala_nome']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($rr['data_especifica'] ?: '-') . ' ' . htmlspecialchars($rr['hora_inicio']) . '-' . htmlspecialchars($rr['hora_fim']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($rr['status']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($rr['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <p style="margin-top:18px"><a href="dashboard.php">Voltar ao Dashboard</a> · <a href="logout.php">Terminar sessão</a></p>
  </main>
</body>
</html>