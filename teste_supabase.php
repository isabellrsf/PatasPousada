<?php
// Lê o arquivo .env
function env($key) {
  static $vars = null;
  if ($vars === null) {
    $vars = [];
    $path = __DIR__ . '/.env';
    if (file_exists($path)) {
      foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line[0] === '#') continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $vars[$k] = $v;
      }
    }
  }
  return $vars[$key] ?? null;
}

// Testa a conexão com Supabase REST API
$url = rtrim(env('SUPABASE_URL'), '/') . '/rest/v1/';
$headers = [
  'apikey: ' . env('SUPABASE_ANON_KEY'),
  'Authorization: Bearer ' . env('SUPABASE_ANON_KEY')
];

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => $headers
]);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

if ($info['http_code'] === 200) {
  echo "✅ Conexão com o Supabase bem-sucedida!";
} else {
  echo "❌ Falha na conexão (HTTP {$info['http_code']})";
}
