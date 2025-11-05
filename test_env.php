<?php
require __DIR__ . '/supabase.php';

echo "<h2>🔍 Teste de conexão com Supabase</h2>";

$url  = env('SUPABASE_URL');
$anon = env('SUPABASE_ANON_KEY');
$serv = env('SUPABASE_SERVICE_KEY');
$ca   = env('SUPABASE_CA_FILE');

if (!$url || !$anon || !$serv) {
  echo "<p style='color:red;'>❌ Falha: Variáveis do .env não foram lidas corretamente.</p>";
  echo "<pre>SUPABASE_URL = " . var_export($url, true) . "</pre>";
  echo "<pre>SUPABASE_ANON_KEY = " . var_export($anon, true) . "</pre>";
  echo "<pre>SUPABASE_SERVICE_KEY = " . ( $serv ? '[definida]' : 'null' ) . "</pre>";
  exit;
} else {
  echo "<p style='color:green;'>✅ Variáveis do .env lidas com sucesso!</p>";
}

if ($ca) {
  $exists = is_file($ca);
  echo $exists
    ? "<p style='color:green;'>✅ SUPABASE_CA_FILE encontrado: ".htmlspecialchars(realpath($ca))."</p>"
    : "<p style='color:red;'>❌ SUPABASE_CA_FILE NÃO encontrado em: ".htmlspecialchars($ca)."</p>";
} else {
  echo "<p style='color:#b58900;'>⚠️ SUPABASE_CA_FILE não definido (usará DEV_NO_SSL_VERIFY se =1)</p>";
}

list($status, $res) = sb_request('GET', '/rest/v1/profiles?limit=1');

if ($status < 300) {
  echo "<p style='color:green;'>✅ Conexão com Supabase REST API funcionando!</p>";
} else {
  echo "<p style='color:red;'>❌ Falha ao conectar com o Supabase REST API. HTTP $status</p>";
}

echo "<p>Resposta (amostra):</p><pre>";
print_r($res);
echo "</pre>";
