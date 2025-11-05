<?php
// session_bridge.php
// Cria sessão PHP a partir do token do Supabase (login feito no front)

if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/supabase.php';

// ====== HEADERS (JSON + CORS básico) ======
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
// Se precisar expor p/ origens diferentes, ajuste:
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Pré-flight (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// ====== LÊ INPUT ======
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);
$access_token  = $in['access_token'] ?? null;
$refresh_token = $in['refresh_token'] ?? null;

if (!$access_token) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'missing access_token']);
  exit;
}

// ====== 1) VALIDA O TOKEN NO SUPABASE AUTH ======
$base   = rtrim(env('SUPABASE_URL'), '/');
$url    = $base . '/auth/v1/user';
$apikey = env('SUPABASE_ANON_KEY') ?: env('SUPABASE_SERVICE_KEY'); // fallback se ANON não existir

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => [
    'Authorization: Bearer ' . $access_token,
    'apikey: ' . $apikey,
  ],
  CURLOPT_TIMEOUT => 30,
]);

// aplica CA bundle / SSL (lê SUPABASE_CA_FILE do .env)
if (function_exists('apply_curl_ssl_opts')) {
  apply_curl_ssl_opts($ch);
}

$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($res === false) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'auth call failed', 'detail' => $err]);
  exit;
}
if ($code >= 300) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'invalid token', 'http' => $code]);
  exit;
}

$user = json_decode($res, true);
$user_id    = $user['id']    ?? null;
$user_email = $user['email'] ?? null;

if (!$user_id) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'user not resolved']);
  exit;
}

// ====== 2) BUSCA PROFILE POR ID (mais confiável que por e-mail) ======
$qs = http_build_query([
  'select' => 'id,full_name,role',
  'id'     => 'eq.' . $user_id,
  'limit'  => 1,
]);

list($st, $rows) = sb_request('GET', "/rest/v1/profiles?$qs");

if ($st >= 300) {
  // ainda cria sessão mínima com dados do Auth
  $_SESSION['profile_id'] = $user_id;
  $_SESSION['full_name']  = $user_email ?: 'Usuário';
  $_SESSION['role']       = 'tutor';
  $_SESSION['email']      = $user_email;
} else {
  $row = $rows[0] ?? null;
  $_SESSION['profile_id'] = $row['id'] ?? $user_id;
  $_SESSION['full_name']  = $row['full_name'] ?: ($user_email ?: 'Usuário');
  $_SESSION['role']       = $row['role'] ?? 'tutor';
  $_SESSION['email']      = $user_email;
}

// (Opcional) guardar tokens do usuário na sessão
$_SESSION['sb_access_token']  = $access_token;
$_SESSION['sb_refresh_token'] = $refresh_token;

echo json_encode([
  'ok'         => true,
  'profile_id' => $_SESSION['profile_id'],
  'full_name'  => $_SESSION['full_name'],
  'role'       => $_SESSION['role'],
]);
