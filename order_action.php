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

// Se aprovado, verificar e reduzir quantidade do produto
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
    
    // Reduzir quantidade do produto
    $new_quantity = $product['quantity'] - $order['quantity'];
    $stmt = $pdo->prepare('UPDATE products SET quantity = ? WHERE id = ?');
    $stmt->execute([$new_quantity, $product['id']]);
}

$stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
$stmt->execute([$status, $order_id]);

header('Location: pedidos.php?msg=' . urlencode('Pedido atualizado.'));
exit;
