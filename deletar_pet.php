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

list($st, $resp) = sb_request('DELETE', "/rest/v1/pets?id=eq.$id");
if ($st >= 300) { http_response_code($st); die('Erro ao excluir'); }

header('Location: meusPets.php?sucesso=' . urlencode('Pet excluído com sucesso!'));
