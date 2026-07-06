<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: index.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';

$product_id = (int)($_GET['product_id'] ?? 0);
$msg = '';
$product = null;

if ($product_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
}

if (!$product) {
    header('Location: inventario.php?msg=' . urlencode('Produto não encontrado.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $add_quantity = (int)($_POST['add_quantity'] ?? 0);
    if ($add_quantity < 1) {
        $msg = 'Introduza uma quantidade válida maior que zero.';
    } else {
        $new_quantity = $product['quantity'] + $add_quantity;
        $stmt = $pdo->prepare('UPDATE products SET quantity = ? WHERE id = ?');
        $stmt->execute([$new_quantity, $product_id]);
        header('Location: inventario.php?msg=' . urlencode('Quantidade adicionada com sucesso.'));
        exit;
    }
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Produto</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Editar Produto</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <p><strong>Produto:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
    <p><strong>Quantidade atual:</strong> <?php echo (int)$product['quantity']; ?></p>
    <p><strong>Descrição:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
    <p><strong>Devolução:</strong> <?php echo $product['returnable'] ? 'Sim' : 'Não'; ?></p>

    <form method="post">
      <label for="add_quantity">Quantidade a adicionar</label>
      <input type="number" id="add_quantity" name="add_quantity" min="1" value="1" required>
      <button type="submit">Adicionar Quantidade</button>
    </form>

    <p style="margin-top:18px"><a href="inventario.php">Voltar ao inventário</a></p>
  </main>
</body>
</html>
