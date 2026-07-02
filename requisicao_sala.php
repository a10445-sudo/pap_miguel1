<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professor') {
    header('Location: index.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';

// Garantir que a tabela de pedidos de sala exista antes de inserir
$pdo->exec("CREATE TABLE IF NOT EXISTS room_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sala_id INT NOT NULL,
  horario_id INT NOT NULL,
  requester_id INT NOT NULL,
  status ENUM('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$horario_id = (int)($_POST['horario_id'] ?? 0);

if (!$horario_id) {
    header('Location: professor.php?msg=' . urlencode('Selecione um horário válido.'));
    exit;
}

// Obter horário e sala
$stmt = $pdo->prepare('SELECT * FROM horarios WHERE id = ? AND disponivel = 1');
$stmt->execute([$horario_id]);
$h = $stmt->fetch();
if (!$h) {
    header('Location: professor.php?msg=' . urlencode('Horário indisponível.'));
    exit;
}

$stmt = $pdo->prepare('INSERT INTO room_requests (sala_id, horario_id, requester_id) VALUES (?, ?, ?)');
$stmt->execute([$h['sala_id'], $horario_id, $_SESSION['user_id']]);

header('Location: professor.php?msg=' . urlencode('Pedido de sala registado.'));
exit;
