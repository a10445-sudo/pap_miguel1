<?php
session_start();
require_once 'db.php';

$nrprocesso = trim($_POST['nrprocesso'] ?? '');
$name = trim($_POST['name'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

if (!$nrprocesso || !ctype_digit($nrprocesso) || !$name || !$password || !in_array($role, ['funcionario','professor'])) {
    header('Location: register.php?msg=' . urlencode('Por favor preencha todos os campos corretamente.'));
    exit;
}

// Verificar número de processo existente
$stmt = $pdo->prepare('SELECT nrprocesso FROM users WHERE nrprocesso = ?');
$stmt->execute([$nrprocesso]);
if ($stmt->fetch()) {
    header('Location: register.php?msg=' . urlencode('Número de processo já registado.'));
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (nrprocesso, name, password, role) VALUES (?, ?, ?, ?)');
try {
    $stmt->execute([$nrprocesso, $name, $hash, $role]);
    header('Location: index.php?msg=' . urlencode('Registo efetuado com sucesso. Pode entrar.'));
    exit;
} catch (Exception $e) {
    header('Location: register.php?msg=' . urlencode('Erro ao registar.'));
    exit;
}
