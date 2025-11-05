<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/supabase.php';

$owner = $_SESSION['profile_id'];
$id = $_POST['id'] ?? null;
if(!$id){ http_response_code(400); die('ID ausente'); }

// Confirma propriedade
$q = http_build_query([
  'id' => 'eq.' . $id,
  'owner_profile_id' => 'eq.' . $owner,
  'select' => 'id',
  'limit' => 1
]);
list($st0, $rows) = sb_request('GET', "/rest/v1/pets?$q");
if ($st0 >= 300 || !$rows) { http_response_code(403); die('Sem permissão'); }

// Monta payload (valores vazios vão como null em campos opcionais)
$payload = [
  'name'   => trim($_POST['name'] ?? ''),
  'species'=> trim($_POST['species'] ?? ''),
  'breed'  => ($_POST['breed'] ?? '') !== '' ? trim($_POST['breed']) : null,
  'sex'    => ($_POST['sex'] ?? '') !== '' ? $_POST['sex'] : null,
  'size'   => ($_POST['size'] ?? '') !== '' ? $_POST['size'] : null,
  'notes'  => ($_POST['notes'] ?? '') !== '' ? trim($_POST['notes']) : null,
  'photo_url' => ($_POST['photo_url'] ?? '') !== '' ? trim($_POST['photo_url']) : null,
  'updated_at' => gmdate('c')
];

list($st, $resp) = sb_request('PATCH', "/rest/v1/pets?id=eq.$id", $payload);
if ($st >= 300) { http_response_code($st); die('Erro ao salvar'); }

header('Location: meusPets.php?sucesso=' . urlencode('Pet atualizado com sucesso!'));
