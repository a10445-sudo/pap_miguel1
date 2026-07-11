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
    $stmt = $pdo->prepare('SELECT id, nome AS name, quantidade AS quantity, descricao AS description, devolvivel AS returnable FROM produtos WHERE id = ?');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
}

if (!$product) {
    header('Location: inventario.php?msg=' . urlencode('Produto não encontrado.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update';

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = ?');
        $stmt->execute([$product_id]);
        header('Location: inventario.php?msg=' . urlencode('Produto eliminado com sucesso.'));
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $returnable = isset($_POST['returnable']) ? 1 : 0;

    if ($name === '' || $quantity < 0) {
        $msg = 'Preencha o nome e uma quantidade válida.';
    } else {
        $stmt = $pdo->prepare('UPDATE produtos SET nome = ?, quantidade = ?, descricao = ?, devolvivel = ? WHERE id = ?');
        $stmt->execute([$name, $quantity, $description, $returnable, $product_id]);
        header('Location: inventario.php?msg=' . urlencode('Produto atualizado com sucesso.'));
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

    <form method="post">
      <input type="hidden" name="action" value="update">

      <label for="name">Nome do produto</label>
      <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

      <label for="quantity">Quantidade</label>
      <input type="number" id="quantity" name="quantity" min="0" value="<?php echo (int)$product['quantity']; ?>" required>

      <label for="description">Descrição</label>
      <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($product['description']); ?>">

      <label class="checkbox-label">
        <input type="checkbox" name="returnable" value="1" <?php echo $product['returnable'] ? 'checked' : ''; ?>>
        Produto com devolução obrigatória
      </label>

      <button type="submit">Guardar alterações</button>
    </form>

    <form method="post" style="margin-top:12px">
      <input type="hidden" name="action" value="delete">
      <button type="submit" style="background:#c0392b;color:white">Eliminar produto</button>
    </form>

    <p style="margin-top:18px"><a href="inventario.php">Voltar ao inventário</a></p>
  </main>
</body>
</html>
