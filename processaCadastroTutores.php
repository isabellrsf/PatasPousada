<?php
// processaCadastroTutores.php
session_start();
require __DIR__ . '/supabase.php';

// Só POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

$nome  = trim($_POST['name'] ?? '');
$cpf   = trim($_POST['cpf'] ?? '');
$data  = trim($_POST['birth_date'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['password'] ?? '';
$conf  = $_POST['confirm_password'] ?? '';

if ($senha !== $conf) {
  header("Location: registrotutores.html?erro=" . urlencode('Senhas não coincidem'));
  exit;
}
if (!$nome || !$cpf || !$data || !$email || !$senha) {
  header("Location: registrotutores.html?erro=" . urlencode('Preencha todos os campos obrigatórios'));
  exit;
}

/**
 * 1) CRIAR USUÁRIO NO AUTH
 *
 * Opção A (sem confirmar e-mail): criar via Admin API
 * - Requer SERVICE ROLE no cabeçalho (NUNCA no front-end)
 * - Usuário entra ativo imediatamente
 */
$use_admin_create = true;

if ($use_admin_create) {
  // Admin: POST /auth/v1/admin/users
  $payloadCreate = [
    'email'    => $email,
    'password' => $senha,
    'user_metadata' => [
      'full_name' => $nome,
      'role'      => 'tutor',
      'cpf'       => $cpf,
      'birth_date'=> $data,
    ],
  ];
  list($stCreate, $resCreate) = sb_request('POST', '/auth/v1/admin/users', $payloadCreate, 'service'); // service role
  if ($stCreate >= 300 || empty($resCreate['id'])) {
    $msg = $resCreate['message'] ?? 'Falha ao criar usuário';
    header("Location: registrotutores.html?erro=" . urlencode($msg));
    exit;
  }
  $user_id = $resCreate['id'];

} else {
  /**
   * Opção B (com confirmação de e-mail): signup normal
   * - POST /auth/v1/signup (com anon key)
   * - Se confirmação estiver habilitada, o user pode vir null até confirmar
   */
  $payloadSignup = [
    'email'    => $email,
    'password' => $senha,
    'data'     => [
      'full_name' => $nome,
      'role'      => 'tutor',
      'cpf'       => $cpf,
      'birth_date'=> $data,
    ],
  ];
  list($stSignup, $resSignup) = sb_request('POST', '/auth/v1/signup', $payloadSignup, 'anon'); // anon key
  if ($stSignup >= 300) {
    $msg = $resSignup['msg'] ?? $resSignup['message'] ?? 'Erro no cadastro';
    header("Location: registrotutores.html?erro=" . urlencode($msg));
    exit;
  }
  // Pode vir em chaves diferentes conforme versão
  $user_id = $resSignup['user']['id'] ?? $resSignup['id'] ?? null;
  if (!$user_id) {
    header("Location: registrotutores.html?sucesso=" . urlencode('Conta criada! Confirme seu e-mail e depois faça login.'));
    exit;
  }
}

/**
 * 2) UPSERT NO PROFILES
 * - id = user_id do Auth
 * - NÃO salve senha/CPF em texto puro no frontend.
 * - Aqui usamos SERVICE ROLE para ignorar RLS com segurança (servidor).
 */
$payloadProfile = [
  'id'         => $user_id,
  'full_name'  => $nome,
  'role'       => 'tutor',
  'cpf'        => $cpf,
  'birth_date' => $data,
  'created_at' => gmdate('c'),
];

list($stProf, $resProf) = sb_request('POST', '/rest/v1/profiles', $payloadProfile, 'service', [
  'Prefer: resolution=merge-duplicates,return=representation'
]);
if ($stProf >= 300) {
  $msg = is_array($resProf) && isset($resProf['message']) ? $resProf['message'] : 'Falha ao salvar perfil';
  header("Location: registrotutores.html?erro=" . urlencode($msg));
  exit;
}

// Sessão local do seu site (opcional)
$_SESSION['profile_id'] = $user_id;
$_SESSION['full_name']  = $nome;

// Redireciona para o cadastro de pets (front com Supabase JS)
header("Location: cadastroPets.html?sucesso=" . urlencode("Cadastro concluído! Agora cadastre seus pets."));
exit;
