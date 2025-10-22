<?php
session_start();
require __DIR__ . '/supabase.php';

$nome  = trim($_POST['name'] ?? '');
$cpf   = trim($_POST['cpf'] ?? '');
$data  = $_POST['birth_date'] ?? '';      // garanta que o form envia birth_date
$email = trim($_POST['email'] ?? '');
$senha = $_POST['password'] ?? '';
$conf  = $_POST['confirm_password'] ?? '';
$city  = trim($_POST['city'] ?? '');
$pets_count = (int)($_POST['pets'] ?? 0);
$residence  = trim($_POST['residence'] ?? '');

if ($senha !== $conf) { header("Location: registroaft.html?erro=Senhas+não+coincidem"); exit; }
if (!$nome || !$cpf || !$data || !$email || !$senha) { header("Location: registroaft.html?erro=Campos+obrigatórios"); exit; }

$payload = [
  'role'           => 'host',
  'full_name'      => $nome,
  'cpf'            => $cpf,
  'birth_date'     => $data,
  'email'          => $email,
  'password_hash'  => password_hash($senha, PASSWORD_DEFAULT),
  'city'           => $city,
  'pets_count'     => $pets_count,
  'residence_type' => $residence,
];

list($st, $res) = sb_request('POST', '/rest/v1/profiles', $payload, true);
if ($st >= 300) { header("Location: registroaft.html?erro=E-mail+ou+CPF+já+existe"); exit; }

$_SESSION['profile_id'] = $res[0]['id'] ?? null;
$_SESSION['full_name']  = $nome;

header('Location: sucesso.html');
exit;
