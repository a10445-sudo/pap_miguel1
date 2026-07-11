<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?msg=' . urlencode('Por favor entre para aceder.'));
    exit;
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: dashboard.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $quantity = $_POST['quantity'] ?? '';
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || !is_numeric($quantity) || (int)$quantity < 1) {
        $msg = 'Preencha o nome e uma quantidade válida maior que zero.';
    } else {
        $returnable = isset($_POST['returnable']) ? 1 : 0;
        $stmt = $pdo->prepare('SELECT id, nome AS name, quantidade AS quantity, descricao AS description, devolvivel AS returnable FROM produtos WHERE nome = ?');
        $stmt->execute([$name]);
        $existing = $stmt->fetch();

        if ($existing) {
            $new_quantity = $existing['quantity'] + (int)$quantity;
            $new_returnable = $existing['returnable'] || $returnable ? 1 : 0;
            $new_description = $description !== '' ? $description : $existing['description'];
            $stmt = $pdo->prepare('UPDATE produtos SET quantidade = ?, descricao = ?, devolvivel = ? WHERE id = ?');
            $stmt->execute([$new_quantity, $new_description, $new_returnable, $existing['id']]);
            header('Location: inventario.php?msg=' . urlencode('Quantidade adicionada ao produto existente.'));
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO produtos (nome, quantidade, descricao, devolvivel) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, (int)$quantity, $description, $returnable]);
        header('Location: inventario.php?msg=' . urlencode('Produto registado com sucesso.'));
        exit;
    }
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registar Produto</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Registar Produto</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <form method="post">
      <label for="name">Nome do produto</label>
      <input type="text" id="name" name="name" required>

      <label for="quantity">Quantidade</label>
      <input type="number" id="quantity" name="quantity" value="1" min="1" required>

      <label for="description">Descrição (opcional)</label>
      <input type="text" id="description" name="description">

      <label class="checkbox-label">
        <input type="checkbox" id="returnable" name="returnable" value="1">
        Produto com devolução obrigatória
      </label>

      <button type="submit">Registar</button>
    </form>

    <p style="margin-top:18px"><a href="inventario.php">Ver inventário</a> · <a href="funcionario.php">Voltar</a></p>
  </main>
</body>
</html>