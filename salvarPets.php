<?php
// salvarPets.php
session_start();
require __DIR__ . '/supabase.php';

// 0) Dono do(s) pet(s)
$owner = $_POST['owner_id'] ?? ($_SESSION['profile_id'] ?? '');
if (!$owner) {
  http_response_code(401);
  echo "Sem owner_id na sessão ou no formulário.";
  exit;
}

// 1) Quantos blocos vieram?
$max = 0;
foreach ($_POST as $k => $v) {
  if (preg_match('/^nome_pet_(\d+)$/', $k, $m)) $max = max($max, (int)$m[1]);
}
if ($max === 0) {
  echo "<script>alert('Adicione pelo menos 1 pet.');history.back();</script>";
  exit;
}

$rows = [];

for ($i = 1; $i <= $max; $i++) {
  $nome = trim($_POST["nome_pet_$i"] ?? '');
  if ($nome === '') continue;

  $especie  = $_POST["especie_pet_$i"] ?? '';
  $outroEsp = $_POST["outro_especie_$i"] ?? '';
  if (strcasecmp($especie, 'Outro') === 0 && $outroEsp) $especie = $outroEsp;

  $raca   = $_POST["raca_pet_$i"]   ?? null;
  $sexo   = $_POST["sexo_pet_$i"]   ?? null;
  $idade  = $_POST["idade_pet_$i"]  ?? null;   // não há campo "age" na sua tabela; manteremos apenas para texto/obs
  $porte  = $_POST["porte_pet_$i"]  ?? null;
  $obs    = $_POST["obs_pet_$i"]    ?? null;

  // 2) Upload opcional de foto
  $fotoKey = "foto_pet_$i";
  $photoUrl = null;
  if (!empty($_FILES[$fotoKey]['tmp_name'])) {
    try {
      $ext   = pathinfo($_FILES[$fotoKey]['name'], PATHINFO_EXTENSION);
      $dest  = "pets/$owner/" . date('Ymd_His') . '_' . uniqid('img_', true) . '.' . $ext;
      // bucket "public" (crie/garanta que exista no Storage e esteja público)
      $photoUrl = upload_to_bucket('public', $dest, $_FILES[$fotoKey]['tmp_name']);
    } catch (Exception $e) {
      // Se quiser, trate erro de upload (não bloqueia inserção)
      // error_log("Upload falhou: " . $e->getMessage());
    }
  }

  // 3) Monta linha para inserir na tabela "pets"
  $rows[] = [
    'owner_id'      => $owner,
    'name'          => $nome,
    'species'       => $especie,
    'breed'         => $raca ?: null,
    'sex'           => $sexo ?: null,
    'size'          => $porte ?: null,
    'special_needs' => $obs ?: null,
    'photo_url'     => $photoUrl,
    // Se depois quiser armazenar idade de forma consistente,
    // adicione um campo na tabela (ex.: age_years integer) e inclua aqui.
  ];
}

// 4) Faz INSERT (em lote) via REST: /rest/v1/pets
if (!$rows) {
  echo "<script>alert('Nada para salvar.');history.back();</script>";
  exit;
}

list($status, $res) = sb_request('POST', '/rest/v1/pets?select=id', $rows);

if ($status >= 200 && $status < 300) {
  $qtd = is_array($res) ? count($res) : 1;
  header('Location: meusPets.php?sucesso=' . urlencode("{$qtd} pet(s) cadastrado(s)"));
  exit;
}

// 5) Erro – mostre para diagnosticar
http_response_code(500);
echo "Falha ao salvar pet(s) (HTTP $status):<br>";
echo "<pre>" . htmlspecialchars(is_string($res) ? $res : print_r($res, true)) . "</pre>";
