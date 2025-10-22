<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/supabase.php';

$owner = $_SESSION['profile_id']; // UUID do tutor
$sucesso = isset($_GET['sucesso']) ? urldecode($_GET['sucesso']) : null;

// Buscar pets do tutor
$q = http_build_query([
  'select' => '*',
  'owner_profile_id' => 'eq.' . $owner,
  'order' => 'id.desc',
]);
list($st, $pets) = sb_request('GET', "/rest/v1/pets?$q");
if ($st >= 300) { http_response_code($st); die('Erro ao carregar pets'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meus Pets - Patas Pousada</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Parkinsans:wght@400;700&display=swap');
    body { font-family: 'Nunito', sans-serif; background-color: #fafafa; margin: 0; color: #333; }
    header { justify-content: center; display: flex; background-color: #fff; padding: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px; width:100%; }
    nav .logo { font-size: 1.5em; font-weight: bold; color: #e2725b; text-decoration: none; font-family: 'Parkinsans', sans-serif; }
    nav ul { list-style: none; display: flex; gap: 15px; }
    nav ul li a { text-decoration: none; color: #333; font-weight: bold; transition: 0.3s; }
    nav ul li a:hover { color: red; }
    .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
    h2 { text-align: center; color: #e2725b; font-family: 'Parkinsans', sans-serif; font-size: 1.8em; margin-bottom: 20px; }
    .add-btn { display: inline-block; background-color: #e2725b; color: white; padding: 10px 18px; border-radius: 25px; text-decoration: none; font-weight: bold; font-family: 'Parkinsans', sans-serif; transition: all .2s; margin-bottom: 20px; }
    .add-btn:hover { background-color: #d65a47; }
    .msg-sucesso { background:#e6ffed; color:#2e7d32; padding:10px 12px; border-radius:8px; font-weight:bold; text-align:center; margin-bottom:20px; }
    .pets-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:20px; }
    .pet-card { background:#fff; border-radius:15px; box-shadow:0 3px 8px rgba(0,0,0,.08); overflow:hidden; transition:.2s; }
    .pet-card:hover { transform: translateY(-4px); box-shadow:0 5px 14px rgba(0,0,0,.15); }
    .pet-photo { width:100%; height:180px; object-fit:cover; background:#f0f0f0; }
    .pet-info { padding:15px; }
    .pet-info h3 { margin:0; color:#e2725b; font-family:'Parkinsans', sans-serif; font-size:1.15em; }
    .pet-info p { margin:6px 0; color:#555; font-size:.95em; line-height:1.4; }
    .empty { text-align:center; color:#777; margin:40px 0; }
    footer { text-align:center; font-size:.85em; color:#888; margin:40px 0 20px; }
  </style>
</head>
<body>
  <header>
    <nav>
      <a href="index.html" class="logo">Patas Pousada</a>
      <ul>
        <li><a href="home_tutor.html">Início</a></li>
        <li><a href="ajuda.html">Ajuda</a></li>
      </ul>
    </nav>
  </header>

  <div class="container">
    <h2>🐾 Meus Pets</h2>

    <?php if ($sucesso): ?>
      <div class="msg-sucesso"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <a href="cadastroPets.html" class="add-btn">➕ Cadastrar Novo Pet</a>

    <div class="pets-grid">
      <?php if (!$pets): ?>
        <p class="empty">Você ainda não cadastrou nenhum pet 😺 <a href="cadastroPets.html">Cadastrar agora</a></p>
      <?php else: ?>
        <?php foreach ($pets as $p):
          $foto = $p['photo_path'] ? public_storage_url('uploads', $p['photo_path']) : 'https://placehold.co/400x300?text=Pet';
        ?>
          <div class="pet-card">
            <img src="<?= htmlspecialchars($foto) ?>" class="pet-photo" alt="Foto do pet">
            <div class="pet-info">
              <h3><?= htmlspecialchars($p['name']) ?></h3>
              <p><strong>Espécie:</strong> <?= htmlspecialchars($p['species']) ?></p>
              <?php if (!empty($p['breed'])): ?><p><strong>Raça:</strong> <?= htmlspecialchars($p['breed']) ?></p><?php endif; ?>
              <p><strong>Idade:</strong> <?= (int)$p['age_years'] ?> anos</p>
              <p><strong>Porte:</strong> <?= htmlspecialchars($p['size']) ?></p>
              <?php if (!empty($p['notes'])): ?><p><strong>Obs:</strong> <?= nl2br(htmlspecialchars($p['notes'])) ?></p><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <footer>© <?= date('Y') ?> Patas Pousada • Todos os direitos reservados</footer>
</body>
</html>
