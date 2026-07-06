<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?msg=' . urlencode('Por favor entre para aceder.'));
    exit;
}
$action = $_POST['action'] ?? '';
$role = isset($_SESSION['user_role']) ? strtolower(trim($_SESSION['user_role'])) : '';
if ($action === 'return_request') {
    if ($role !== 'professor') {
        header('Location: dashboard.php?msg=' . urlencode('Acesso negado.'));
        exit;
    }
} else {
    if ($role !== 'funcionario' && $role !== 'administrador') {
        header('Location: dashboard.php?msg=' . urlencode('Acesso negado.'));
        exit;
    }
}

$type = $_POST['type'] ?? 'product';

if ($type === 'product') {
    $order_id = (int)($_POST['order_id'] ?? 0);
        if (!$order_id || !in_array($action, ['approve', 'reject', 'return', 'return_request'])) {
            header('Location: pedidos.php?msg=' . urlencode('Parâmetros inválidos.'));
            exit;
        }

        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        if (!$order) {
            header('Location: pedidos.php?msg=' . urlencode('Pedido não encontrado.'));
            exit;
        }

        if ($action === 'return_request') {
            if ($order['status'] !== 'aprovado') {
                header('Location: professor.php?msg=' . urlencode('Apenas pedidos aprovados podem solicitar devolução.'));
                exit;
            }
            if (!$order['return_required']) {
                header('Location: professor.php?msg=' . urlencode('Este produto não exige devolução.'));
                exit;
            }
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute(['devolucao_pendente', $order_id]);
            header('Location: professor.php?msg=' . urlencode('Pedido de devolução registado.')); 
            exit;
        }

        if ($action === 'return') {
            if ($order['status'] !== 'devolucao_pendente') {
                header('Location: devolucoes.php?msg=' . urlencode('Apenas devoluções pendentes podem ser marcadas como devolvidas.'));
                exit;
            }
            if (!$order['return_required']) {
                header('Location: devolucoes.php?msg=' . urlencode('Este produto não exige devolução.'));
                exit;
            }
            if ($order['product_id']) {
                $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
                $stmt->execute([$order['product_id']]);
            } else {
                $stmt = $pdo->prepare('SELECT * FROM products WHERE name = ?');
                $stmt->execute([$order['product_name']]);
            }
            $product = $stmt->fetch();
            if (!$product) {
                header('Location: devolucoes.php?msg=' . urlencode('Produto não encontrado no inventário.'));
                exit;
            }
            $new_quantity = $product['quantity'] + $order['quantity'];
            $stmt = $pdo->prepare('UPDATE products SET quantity = ? WHERE id = ?');
            $stmt->execute([$new_quantity, $product['id']]);
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute(['devolvido', $order_id]);
            header('Location: devolucoes.php?msg=' . urlencode('Produto devolvido e inventário atualizado.'));
            exit;
        }

        if ($order['status'] !== 'pendente') {
            header('Location: pedidos.php?msg=' . urlencode('O pedido já foi processado.'));
            exit;
        }

    $status = $action === 'approve' ? 'aprovado' : 'rejeitado';
    if ($action === 'approve') {
        if ($order['product_id']) {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$order['product_id']]);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE name = ?');
            $stmt->execute([$order['product_name']]);
        }
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
        if ($new_quantity > 0) {
            $stmt = $pdo->prepare('UPDATE products SET quantity = ? WHERE id = ?');
            $stmt->execute([$new_quantity, $product['id']]);
        } else {
            if ($product['returnable']) {
                $stmt = $pdo->prepare('UPDATE products SET quantity = ? WHERE id = ?');
                $stmt->execute([$new_quantity, $product['id']]);
            } else {
                $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
                $stmt->execute([$product['id']]);
            }
        }
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
    // If approved, validate horario availability before marking as unavailable
    if ($action === 'approve') {
        $stmt = $pdo->prepare('SELECT * FROM horarios WHERE id = ?');
        $stmt->execute([$req['horario_id']]);
        $horario = $stmt->fetch();
        if (!$horario || !$horario['disponivel']) {
            header('Location: pedidos.php?msg=' . urlencode('Não é possível aprovar: o horário já não está disponível.'));
            exit;
        }
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
