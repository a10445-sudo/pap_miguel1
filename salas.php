<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?msg=' . urlencode('Por favor entre para aceder.'));
    exit;
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'funcionario') {
    header('Location: dashboard.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';

// Garantir que a tabela de salas exista
$pdo->exec("CREATE TABLE IF NOT EXISTS salas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  capacidade INT DEFAULT NULL,
  localizacao VARCHAR(255) DEFAULT NULL,
  created_by INT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$stmt = $pdo->query('SELECT * FROM salas ORDER BY nome');
$salas = $stmt->fetchAll();

$stmt = $pdo->query('SELECT h.*, s.nome AS sala_nome FROM horarios h JOIN salas s ON s.id = h.sala_id WHERE h.disponivel = 1 ORDER BY s.nome, h.hora_inicio');
$horarios = $stmt->fetchAll();

$name = htmlspecialchars($_SESSION['user_name']);
$msg = $_GET['msg'] ?? ''; 
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Salas</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <h1>Salas Disponíveis</h1>
    <?php if ($msg): ?>
      <div class="flash"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if (count($salas) === 0): ?>
      <p>Não existem salas registadas.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Nome</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Capacidade</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Localização</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($salas as $s): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($s['nome']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo $s['capacidade'] ? (int)$s['capacidade'] : '-'; ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($s['localizacao'] ?: '-'); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <h2>Horários Disponíveis</h2>
    <?php if (count($horarios) === 0): ?>
      <p>Não existem horários disponíveis no momento.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Sala</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Dia/Data</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Início</th>
            <th style="text-align:left;padding:8px;border-bottom:1px solid #eee">Fim</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($horarios as $h): ?>
          <tr>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($h['sala_nome']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo $h['dia_semana'] ? htmlspecialchars($h['dia_semana']) : htmlspecialchars($h['data_especifica']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($h['hora_inicio']); ?></td>
            <td style="padding:8px;border-bottom:1px solid #f2f2f2"><?php echo htmlspecialchars($h['hora_fim']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <p style="margin-top:18px"><a href="funcionario.php">Voltar ao Painel do Funcionário</a></p>
  </main>
</body>
</html>
