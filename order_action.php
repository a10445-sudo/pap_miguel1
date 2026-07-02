<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?msg=' . urlencode('Por favor entre para aceder.'));
    exit;
}
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'funcionario' && $_SESSION['user_role'] !== 'administrador')) {
    header('Location: dashboard.php?msg=' . urlencode('Acesso negado.'));
    exit;
}

// Determine if action is for a product order or a room request
$type = $_POST['type'] ?? 'product';
$action = $_POST['action'] ?? '';

if ($type === 'product') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    if (!$order_id || !in_array($action, ['approve', 'reject'])) {
        header('Location: pedidos.php?msg=' . urlencode('Parâmetros inválidos.'));
        exit;
    }
    $status = $action === 'approve' ? 'aprovado' : 'rejeitado';

    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if (!$order) {
        header('Location: pedidos.php?msg=' . urlencode('Pedido não encontrado.'));
        exit;
    }
    if ($order['status'] !== 'pendente') {
        header('Location: pedidos.php?msg=' . urlencode('O pedido já foi processado.'));
        exit;
    }
    if ($action === 'approve') {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE name = ?');
        $stmt->execute([$order['product_name']]);
        $product = $stmt->fetch();
        if (!$product) {
            header('Location: pedidos.php?msg=' . urlencode('Produto não encontrado no inventário.'));
            exit;
        }
        if ($product['quantity'] < $order['quantity']) {
            header('Location: pedidos.php?msg=' . urlencode('Quantidade insuficiente em inventário.'));
            exit;
        }
        $new_quantity = $product['quantity'] - $order['quantity'];
        $stmt = $pdo->prepare('UPDATE products SET quantity = ? WHERE id = ?');
        $stmt->execute([$new_quantity, $product['id']]);
    }
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $order_id]);
    header('Location: pedidos.php?msg=' . urlencode('Pedido de produto atualizado.'));
    exit;

} elseif ($type === 'room') {
    $request_id = (int)($_POST['request_id'] ?? 0);
    if (!$request_id || !in_array($action, ['approve', 'reject'])) {
        header('Location: pedidos.php?msg=' . urlencode('Parâmetros inválidos.'));
        exit;
    }
    $status = $action === 'approve' ? 'aprovado' : 'rejeitado';

    $stmt = $pdo->prepare('SELECT * FROM room_requests WHERE id = ?');
    $stmt->execute([$request_id]);
    $req = $stmt->fetch();
    if (!$req) {
        header('Location: pedidos.php?msg=' . urlencode('Pedido de sala não encontrado.'));
        exit;
    }
    if ($req['status'] !== 'pendente') {
        header('Location: pedidos.php?msg=' . urlencode('O pedido já foi processado.'));
        exit;
    }
    // If approved, mark horario as unavailable
    if ($action === 'approve') {
        $stmt = $pdo->prepare('UPDATE horarios SET disponivel = 0 WHERE id = ?');
        $stmt->execute([$req['horario_id']]);
    }
    $stmt = $pdo->prepare('UPDATE room_requests SET status = ? WHERE id = ?');
    $stmt->execute([$status, $request_id]);
    header('Location: pedidos.php?msg=' . urlencode('Pedido de sala atualizado.'));
    exit;

} else {
    header('Location: pedidos.php?msg=' . urlencode('Tipo desconhecido.'));
    exit;
}
