<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?msg=' . urlencode('Por favor entre para aceder.'));
    exit;
}
$name = htmlspecialchars($_SESSION['user_name']);
$role = $_SESSION['user_role'];
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - PAP</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Bem-vindo, <?php echo $name; ?>!</h1>
    <p>Tipo: <strong><?php echo ($role === 'professor') ? 'Professor' : (($role === 'administrador') ? 'Administrador' : 'Funcionário'); ?></strong></p>

    <?php if ($role === 'funcionario'): ?>
      <p><a class="button" href="funcionario.php">Aceder ao Painel de Funcionário</a></p>
    <?php elseif ($role === 'administrador'): ?>
      <p><a class="button" href="administrador.php">Aceder ao Painel de Administrador</a></p>
    <?php endif; ?>

    <p><a href="logout.php">Terminar sessão</a></p>
  </main>
</body>
</html>
