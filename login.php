<?php
session_start();
require_once 'db.php';

$nrprocesso = (int)trim($_POST['nrprocesso'] ?? '');
$password = $_POST['password'] ?? '';

if (!$nrprocesso || !$password) {
    header('Location: index.php?msg=' . urlencode('Número de processo e palavra-passe são obrigatórios.'));
    exit;
}

$stmt = $pdo->prepare('SELECT numero_processo AS id, palavra_passe AS password, nome AS name, tipo AS role FROM utilizadores WHERE numero_processo = ?');
$stmt->execute([$nrprocesso]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    header('Location: index.php?msg=' . urlencode('Credenciais inválidas.'));
    exit;
}

// Login bem-sucedido
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];

header('Location: dashboard.php');
exit;
