<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/supabase.php';

$owner = $_SESSION['profile_id'];
$id = $_GET['id'] ?? null;
if(!$id){ http_response_code(400); die('ID ausente'); }

// Carrega pet garantindo que pertence ao dono
$q = http_build_query([
  'id' => 'eq.' . $id,
  'owner_profile_id' => 'eq.' . $owner,
  'select' => '*',
]);
list($st, $rows) = sb_request('GET', "/rest/v1/pets?$q");
if ($st >= 300 || !$rows) { http_response_code(404); die('Pet não encontrado'); }
$pet = $rows[0];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Pet - Patas Pousada</title>
  <style>
    body{font-family:Nunito,Arial,sans-serif;background:#fafafa;margin:0;color:#333}
    .wrap{max-width:720px;margin:30px auto;background:#fff;padding:20px;border-radius:14px;box-shadow:0 3px 12px rgba(0,0,0,.08)}
    h2{margin-top:0;color:#e2725b}
    label{display:block;margin:8px 0 4px}
    input,select,textarea{width:100%;padding:10px;border:1px solid #ddd;border-radius:10px}
    textarea{min-height:100px}
    .actions{display:flex;gap:10px;justify-content:flex-end;margin-top:14px}
    .btn{border-radius:10px;padding:10px 14px;border:2px solid transparent;cursor:pointer;font-weight:800}
    .btn-secondary{background:#fff;border-color:#e2725b;color:#e2725b}
    .btn-primary{background:#e2725b;color:#fff;border-color:#e2725b}
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Editar Pet</h2>
    <form action="salvar_pet.php" method="post">
      <input type="hidden" name="id" value="<?= htmlspecialchars($pet['id']) ?>">
      <label>Nome</label>
      <input name="name" required value="<?= htmlspecialchars($pet['name'] ?? '') ?>">

      <label>Espécie</label>
      <input name="species" required value="<?= htmlspecialchars($pet['species'] ?? '') ?>">

      <label>Raça</label>
      <input name="breed" value="<?= htmlspecialchars($pet['breed'] ?? '') ?>">

      <label>Sexo</label>
      <select name="sex">
        <?php
          $sex = $pet['sex'] ?? '';
          $opts = ['', 'Macho', 'Fêmea'];
          foreach($opts as $o){
            $sel = ($o === $sex) ? 'selected' : '';
            $label = $o ?: 'Selecione…';
            echo "<option value=\"".htmlspecialchars($o)."\" $sel>$label</option>";
          }
        ?>
      </select>

      <label>Porte</label>
      <select name="size">
        <?php
          $size = $pet['size'] ?? '';
          $opts = ['', 'Pequeno', 'Médio', 'Grande'];
          foreach($opts as $o){
            $sel = ($o === $size) ? 'selected' : '';
            $label = $o ?: 'Selecione…';
            echo "<option value=\"".htmlspecialchars($o)."\" $sel>$label</option>";
          }
        ?>
      </select>

      <label>Observações / Necessidades especiais</label>
      <textarea name="notes"><?= htmlspecialchars($pet['notes'] ?? $pet['special_needs'] ?? '') ?></textarea>

      <label>Foto (URL)</label>
      <input name="photo_url" type="url" value="<?= htmlspecialchars($pet['photo_url'] ?? '') ?>">

      <div class="actions">
        <a class="btn btn-secondary" href="meusPets.php">Cancelar</a>
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
    </form>
  </div>
</body>
</html>
