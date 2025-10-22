<?php
// supabase.php

// -------------- Leitura de variáveis do .env --------------
function env($key) {
  static $vars = null;
  if ($vars === null) {
    $vars = [];
    $path = __DIR__ . '/.env';
    if (file_exists($path)) {
      foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line === '' || $line[0] === '#') continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $vars[$k] = $v;
      }
    }
  }
  return $vars[$key] ?? null;
}

// -------------- Chamada REST genérica ao Supabase (server-side) --------------
function sb_request($method, $path, $json = null, array $extraHeaders = []) {
  $base = rtrim(env('SUPABASE_URL'), '/');
  $url  = $base . $path;

  $headers = array_merge([
    'Authorization: Bearer ' . env('SUPABASE_SERVICE_KEY'),
    'apikey: ' . env('SUPABASE_SERVICE_KEY'),
    'Content-Type: application/json',
    'Prefer: return=representation',
  ], $extraHeaders);

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST  => $method,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => ($json !== null ? json_encode($json) : null),
  ]);

  $body   = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err    = curl_error($ch);
  curl_close($ch);

  if ($body === false) return [$status ?: 0, ['error' => $err]];

  $decoded = json_decode($body, true);
  if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) $decoded = $body;

  return [$status, $decoded];
}

// -------------- Upload para Storage (bucket público) --------------
function upload_to_bucket($bucket, $destPath, $localTmpPath) {
  $base = rtrim(env('SUPABASE_URL'), '/');
  $url  = "$base/storage/v1/object/$bucket/$destPath";

  $ch = curl_init($url);
  $headers = [
    'Authorization: Bearer ' . env('SUPABASE_SERVICE_KEY'),
    'apikey: ' . env('SUPABASE_SERVICE_KEY'),
    'Content-Type: application/octet-stream',
  ];
  curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST   => 'POST',
    CURLOPT_HTTPHEADER      => $headers,
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_POSTFIELDS      => file_get_contents($localTmpPath),
  ]);

  $res  = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($code >= 300) throw new Exception("Falha no upload ($code): $res");

  // URL pública (se o bucket for público)
  return "$base/storage/v1/object/public/$bucket/$destPath";
}
