<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: index.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';

$sala_id = (int)($_GET['sala_id'] ?? 0);
$msg = '';
$sala = null;

if ($sala_id > 0) {
    $stmt = $pdo->prepare('SELECT id, nome, capacidade, localizacao FROM salas WHERE id = ?');
    $stmt->execute([$sala_id]);
    $sala = $stmt->fetch();
}

if (!$sala) {
    header('Location: salas_admin.php?msg=' . urlencode('Sala não encontrada.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update';

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM salas WHERE id = ?');
        $stmt->execute([$sala_id]);
        header('Location: salas_admin.php?msg=' . urlencode('Sala eliminada com sucesso.'));
        exit;
    }

    $nome = trim($_POST['nome'] ?? '');
    $capacidade = (int)($_POST['capacidade'] ?? 0);
    $localizacao = trim($_POST['localizacao'] ?? '');

    if ($nome === '' || $capacidade < 1 || $localizacao === '') {
        $msg = 'Preencha todos os campos corretamente.';
    } else {
        $stmt = $pdo->prepare('UPDATE salas SET nome = ?, capacidade = ?, localizacao = ? WHERE id = ?');
        $stmt->execute([$nome, $capacidade, $localizacao, $sala_id]);
        header('Location: salas_admin.php?msg=' . urlencode('Sala atualizada com sucesso.'));
        exit;
    }
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Sala</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Editar Sala</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="action" value="update">

      <label for="nome">Nome da sala</label>
      <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($sala['nome']); ?>" required>

      <label for="capacidade">Capacidade</label>
      <input type="number" id="capacidade" name="capacidade" min="1" value="<?php echo (int)$sala['capacidade']; ?>" required>

      <label for="localizacao">Localização</label>
      <input type="text" id="localizacao" name="localizacao" value="<?php echo htmlspecialchars($sala['localizacao']); ?>" required>

      <button type="submit">Guardar alterações</button>
    </form>

    <form method="post" style="margin-top:12px">
      <input type="hidden" name="action" value="delete">
      <button type="submit" style="background:#c0392b;color:white">Eliminar sala</button>
    </form>

    <p style="margin-top:18px"><a href="salas_admin.php">Voltar</a></p>
  </main>
</body>
</html>
