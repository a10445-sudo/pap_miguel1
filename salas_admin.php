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

// Load salas and horarios
$stmt = $pdo->query('SELECT * FROM salas ORDER BY id DESC');
$salas = $stmt->fetchAll();

$stmt = $pdo->query("SELECT h.*, s.nome AS sala_nome FROM horarios h JOIN salas s ON s.id = h.sala_id WHERE h.dia_semana IS NULL OR h.dia_semana IN ('segunda','terca','quarta','quinta','sexta') ORDER BY h.id DESC");
$horarios = $stmt->fetchAll();

$msg = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestão de Salas</title>
  <link rel="stylesheet" href="styles.css">
  <style>form.inline{display:flex;gap:8px;align-items:center}</style>
</head>
<body>
  <main class="container">
    <h1>Gestão de Salas</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <h2>Adicionar Sala</h2>
    <form method="post" action="nova_sala.php" class="inline">
      <input name="nome" placeholder="Nome da sala" required>
      <input name="capacidade" type="number" placeholder="Capacidade" min="1" required>
      <input name="localizacao" placeholder="Localização" required>
      <button type="submit">Adicionar Sala</button>
    </form>

    <h2>Adicionar Horário</h2>
    <form method="post" action="horario.php" class="inline">
      <select name="sala_id" required>
        <option value="">Selecionar sala</option>
        <?php foreach ($salas as $s): ?>
          <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nome']); ?></option>
        <?php endforeach; ?>
      </select>
      <select name="dia_semana">
        <option value="">Data específica</option>
        <option value="segunda">Segunda</option>
        <option value="terca">Terça</option>
        <option value="quarta">Quarta</option>
        <option value="quinta">Quinta</option>
        <option value="sexta">Sexta</option>
      </select>
      <input type="date" name="data_especifica">
      <input type="time" name="hora_inicio" required>
      <input type="time" name="hora_fim" required>
      <button type="submit">Adicionar Horário</button>
    </form>

    <h2>Salas</h2>
    <?php if (count($salas) === 0): ?>
      <p>Sem salas registadas.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($salas as $s): ?>
          <li>
            <?php echo htmlspecialchars($s['nome']); ?> — <?php echo htmlspecialchars($s['localizacao']); ?> (<?php echo $s['capacidade'] ? (int)$s['capacidade'] : '-'; ?>)
            · <a href="editar_sala.php?sala_id=<?php echo (int)$s['id']; ?>">Modificar</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <h2>Horários</h2>
    <?php if (count($horarios) === 0): ?>
      <p>Sem horários registados.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr><th>Sala</th><th>Dia/Data</th><th>Início</th><th>Fim</th><th>Disponível</th></tr>
        </thead>
        <tbody>
        <?php foreach ($horarios as $h): ?>
          <tr>
            <td style="padding:6px;border-bottom:1px solid #eee"><?php echo htmlspecialchars($h['sala_nome']); ?></td>
            <td style="padding:6px;border-bottom:1px solid #eee"><?php echo $h['dia_semana'] ? htmlspecialchars($h['dia_semana']) : htmlspecialchars($h['data_especifica']); ?></td>
            <td style="padding:6px;border-bottom:1px solid #eee"><?php echo htmlspecialchars($h['hora_inicio']); ?></td>
            <td style="padding:6px;border-bottom:1px solid #eee"><?php echo htmlspecialchars($h['hora_fim']); ?></td>
            <td style="padding:6px;border-bottom:1px solid #eee"><?php echo $h['disponivel'] ? 'Sim' : 'Não'; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <p style="margin-top:18px"><a href="administrador.php">Voltar</a></p>
  </main>
</body>
</html>
