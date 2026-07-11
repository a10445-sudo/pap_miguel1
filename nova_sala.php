<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: index.php?msg=' . urlencode('Acesso negado.'));
    exit;
}
require 'db.php';

$nome = trim($_POST['nome'] ?? '');
$cap = (int)($_POST['capacidade'] ?? 0);
$loc = trim($_POST['localizacao'] ?? '');

if (!$nome || !$cap || !$loc) {
    header('Location: salas_admin.php?msg=' . urlencode('Por favor preencha todos os campos de sala.'));
    exit;
}

$stmt = $pdo->prepare('INSERT INTO salas (nome, capacidade, localizacao, criado_por) VALUES (?, ?, ?, ?)');
$stmt->execute([$nome, $cap, $loc, isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null]);

header('Location: salas_admin.php?msg=' . urlencode('Sala adicionada.'));
exit;
