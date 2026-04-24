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
$name = htmlspecialchars($_SESSION['user_name']);
$msg = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Painel de Administrador</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Bem-vindo, <?php echo $name; ?>!</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <p>Escolha uma opção:</p>

    <div class="icon-grid">
      <a class="icon-card" href="registar_produto.php">
        <span class="emoji">➕</span>
        <h3>Registar Produto</h3>
      </a>

      <a class="icon-card" href="inventario.php">
        <span class="emoji">📦</span>
        <h3>Ver Inventário</h3>
      </a>

      <a class="icon-card" href="pedidos.php">
        <span class="emoji">🧾</span>
        <h3>Ver Pedidos</h3>
      </a>
    </div>

    <p style="margin-top:18px"><a href="dashboard.php">Voltar ao Dashboard</a> · <a href="logout.php">Terminar sessão</a></p>
  </main>
</body>
</html>