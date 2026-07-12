<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: index.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';

$sala_id = (int)($_POST['sala_id'] ?? 0);
$data = $_POST['data_especifica'] ?? null;
$inicio = $_POST['hora_inicio'] ?? null;
$fim = $_POST['hora_fim'] ?? null;

if (!$sala_id || !$inicio || !$fim) {
    header('Location: salas_admin.php?msg=' . urlencode('Dados inválidos para horário.'));
    exit;
}

$stmt = $pdo->prepare('INSERT INTO horarios (sala_id, data_especifica, hora_inicio, hora_fim, criado_por) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$sala_id, $data ?: null, $inicio, $fim, isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null]);

header('Location: salas_admin.php?msg=' . urlencode('Horário adicionado.'));
exit;
