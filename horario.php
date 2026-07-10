<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: index.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';

$sala_id = (int)($_POST['sala_id'] ?? 0);
$dia = $_POST['dia_semana'] ?? null;
$data = $_POST['data_especifica'] ?? null;
$inicio = $_POST['hora_inicio'] ?? null;
$fim = $_POST['hora_fim'] ?? null;

$allowedWeekdays = ['segunda', 'terca', 'quarta', 'quinta', 'sexta'];
if ($dia !== null && $dia !== '' && !in_array($dia, $allowedWeekdays, true)) {
    header('Location: salas_admin.php?msg=' . urlencode('Dia da semana inválido.'));
    exit;
}

if (!$sala_id || !$inicio || !$fim) {
    header('Location: salas_admin.php?msg=' . urlencode('Dados inválidos para horário.'));
    exit;
}

$stmt = $pdo->prepare('INSERT INTO horarios (sala_id, dia_semana, data_especifica, hora_inicio, hora_fim, created_by) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute([$sala_id, $dia ?: null, $data ?: null, $inicio, $fim, $_SESSION['user_id']]);

header('Location: salas_admin.php?msg=' . urlencode('Horário adicionado.'));
exit;
