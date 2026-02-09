<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?msg=' . urlencode('Por favor entre para aceder.'));
    exit;
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'funcionario') {
    header('Location: dashboard.php?msg=' . urlencode('Acesso negado.'));
    exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);
action:
$action = $_POST['action'] ?? '';

if (!$order_id || !in_array($action, ['approve', 'reject'])) {
    header('Location: pedidos.php?msg=' . urlencode('Parâmetros inválidos.'));
    exit;
}

// Map action to status
$status = $action === 'approve' ? 'aprovado' : 'rejeitado';

// Verificar existência do pedido
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$order_id]);
$order = $stmt->fetch();
if (!$order) {
    header('Location: pedidos.php?msg=' . urlencode('Pedido não encontrado.'));
    exit;
}

// Apenas atualizar se estiver pendente
if ($order['status'] !== 'pendente') {
    header('Location: pedidos.php?msg=' . urlencode('O pedido já foi processado.'));
    exit;
}

$stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
$stmt->execute([$status, $order_id]);

header('Location: pedidos.php?msg=' . urlencode('Pedido atualizado.'));
exit;
