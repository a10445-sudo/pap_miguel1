<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
$flash = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - PAP</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Entrar</h1>
    <?php if ($flash): ?>
      <p class="flash"><?php echo $flash; ?></p>
    <?php endif; ?>

    <form action="login.php" method="post">
      <label for="nrprocesso">Número de processo</label>
      <input type="number" id="nrprocesso" name="nrprocesso" required>

      <label for="password">Palavra-passe</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Entrar</button>
    </form>

    <p>Não tem conta? <a href="register.php">Registe-se como Funcionário, Professor ou Administrador</a></p>
  </main>
</body>
</html>
