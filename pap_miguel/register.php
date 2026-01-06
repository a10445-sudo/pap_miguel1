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
  <title>Registo - PAP</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Registar</h1>
    <?php if ($flash): ?>
      <p class="flash"><?php echo $flash; ?></p>
    <?php endif; ?>

    <form action="register_handler.php" method="post">
      <label for="name">Nome</label>
      <input type="text" id="name" name="name" required>

      <label for="nrprocesso">Número de processo</label>
      <input type="number" id="nrprocesso" name="nrprocesso" required>

      <label for="password">Palavra-passe</label>
      <input type="password" id="password" name="password" required>

      <label for="role">Tipo</label>
      <select id="role" name="role" required>
        <option value="funcionario">Funcionário</option>
        <option value="professor">Professor</option>
      </select>

      <button type="submit">Registar</button>
    </form>

    <p>Já tem conta? <a href="index.php">Entrar</a></p>
  </main>
</body>
</html>
