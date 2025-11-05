<?php
// processaCadastroTutores.php
session_start();
require __DIR__ . '/supabase.php';

// Só POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

// Coleta e saneamento básico
$nome  = trim($_POST['name'] ?? '');
$cpf   = preg_replace('/\D+/', '', $_POST['cpf'] ?? ''); // só dígitos
$data  = trim($_POST['birth_date'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['password'] ?? '';
$conf  = $_POST['confirm_password'] ?? '';

// Normaliza data: aceita "dd/mm/yyyy" ou "yyyy-mm-dd"
if ($data) {
  if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $data)) {
    [$d, $m, $y] = explode('/', $data);
    $data = sprintf('%04d-%02d-%02d', (int)$y, (int)$m, (int)$d);
  } elseif (!preg_match('#^\d{4}-\d{2}-\d{2}$#', $data)) {
    header("Location: registrotutores.html?erro=" . urlencode('Data de nascimento inválida'));
    exit;
  }
}

// Validações
if ($senha !== $conf) {
  header("Location: registrotutores.html?erro=" . urlencode('Senhas não coincidem'));
  exit;
}
if (!$nome || !$cpf || !$data || !$email || !$senha) {
  header("Location: registrotutores.html?erro=" . urlencode('Preencha todos os campos obrigatórios'));
  exit;
}

// 1) CRIAR USUÁRIO VIA ADMIN API (SERVICE ROLE) — dispara o TRIGGER
$payloadCreate = [
  'email'    => $email,
  'password' => $senha,
  'user_metadata' => [
    'full_name'  => $nome,
    'role'       => 'tutor',
    'cpf'        => $cpf,   // lido pelo trigger se necessário
    'birth_date' => $data,  // lido pelo trigger se necessário
  ],
];

[$stCreate, $resCreate] = sb_request('POST', '/auth/v1/admin/users', $payloadCreate);

if ($stCreate >= 300 || empty($resCreate['id'])) {
  $msg = $resCreate['message'] ?? $resCreate['error_description'] ?? 'Falha ao criar usuário';
  header("Location: registrotutores.html?erro=" . urlencode($msg));
  exit;
}

$user_id = $resCreate['id'];

// 2) UPSERT EM profiles (idempotente, caso o trigger já tenha inserido)
$payloadProfile = [
  'id'         => $user_id,
  'email'      => $email,     // obrigatório na sua tabela (unique not null)
  'full_name'  => $nome,
  'role'       => 'tutor',
  'cpf'        => $cpf,
  'birth_date' => $data,
  'created_at' => gmdate('c'),
];

// sobrescreve o Prefer padrão pra permitir merge-duplicates + representation
[$stProf, $resProf] = sb_request(
  'POST',
  '/rest/v1/profiles',
  $payloadProfile,
  ['Prefer: resolution=merge-duplicates,return=representation']
);

if ($stProf >= 300) {
  $msg = (is_array($resProf) && isset($resProf['message']))
    ? $resProf['message']
    : 'Falha ao salvar perfil';
  header("Location: registrotutores.html?erro=" . urlencode($msg));
  exit;
}

// 3) Sessão local opcional
$_SESSION['profile_id'] = $user_id;
$_SESSION['full_name']  = $nome;

// 4) Redireciona para cadastro de pets
header("Location: cadastroPets.html?sucesso=" . urlencode("Cadastro concluído! Agora cadastre seus pets."));
exit;
