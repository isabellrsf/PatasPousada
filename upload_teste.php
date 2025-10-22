<?php
require __DIR__ . '/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  // Form simples pra escolher uma imagem
  echo '<form method="post" enctype="multipart/form-data" style="margin:30px">
          <input type="file" name="foto" accept="image/*" required>
          <button>Enviar</button>
        </form>';
  exit;
}

try {
  if (empty($_FILES['foto']['tmp_name'])) {
    throw new Exception('Arquivo não recebido.');
  }

  // crie um caminhozinho organizado no bucket
  $ext  = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
  $dest = 'pets/' . date('Y/m/') . uniqid('img_') . '.' . $ext;

  $publicUrl = upload_to_bucket('uploads', $dest, $_FILES['foto']['tmp_name']);

  echo "✅ Upload OK!<br><br>";
  echo "<img src=\"$publicUrl\" width=\"240\" style=\"border:1px solid #eee\"><br><br>";
  echo "<code>$publicUrl</code>";

} catch (Exception $e) {
  http_response_code(500);
  echo "❌ Erro: " . htmlspecialchars($e->getMessage());
}
