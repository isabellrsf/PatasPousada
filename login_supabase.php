<?php
session_start();
require __DIR__ . '/supabase.php';

$email = trim($_POST['email'] ?? '');
$senha = $_POST['password'] ?? '';

if (!$email || !$senha) {
  echo "<script>alert('Informe e-mail e senha.');history.back();</script>";
  exit;
}

$qs = http_build_query([
  'select' => 'id,full_name,password_hash',
  'email'  => 'eq.' . $email,
  'limit'  => 1,
]);

list($st, $rows) = sb_request('GET', "/rest/v1/profiles?$qs");
if ($st >= 300 || empty($rows)) {
  echo "<script>alert('E-mail não encontrado.');window.location='login.html';</script>";
  exit;
}

$user = $rows[0];
if (!password_verify($senha, $user['password_hash'])) {
  echo "<script>alert('Senha incorreta.');window.location='login.html';</script>";
  exit;
}

$_SESSION['profile_id'] = $user['id'];
$_SESSION['full_name']  = $user['full_name'];

header('Location: home_tutor.html');
exit;
