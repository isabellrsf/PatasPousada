<?php
session_start();
require 'auth.php';
require 'conexao.php';

// ID do tutor
$tutor_id = (int)$_SESSION['id_tutor'];

// Consulta pets do tutor
$stmt = $conn->prepare("SELECT * FROM pets WHERE tutor_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$res = $stmt->get_result();

$sucesso = isset($_GET['sucesso']) ? urldecode($_GET['sucesso']) : null;
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
    nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
    nav .logo { font-size: 1.5em; font-weight: bold; color: #e2725b; text-decoration: none; font-family: 'Parkinsans', sans-serif; }
    nav ul { list-style: none; display: flex; gap: 15px; }
    nav ul li a { text-decoration: none; color: #333; font-weight: bold; transition: 0.3s; }
    nav ul li a:hover { color: red; }
    .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
    h2 { text-align: center; color: #e2725b; font-family: 'Parkinsans', sans-serif; font-size: 1.8em; margin-bottom: 30px; }
    .add-btn { display: inline-block; background-color: #e2725b; color: white; padding: 10px 18px; border-radius: 25px; text-decoration: none; font-weight: bold; font-family: 'Parkinsans', sans-serif; transition: all 0.3s ease; margin-bottom: 20px; box-shadow: 0 3px 8px rgba(226,114,91,0.3); }
    .add-btn:hover { background-color: #d65a47; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(226,114,91,0.4); }
    .pets-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
    .pet-card { background-color: #fff; border-radius: 15px; box-shadow: 0 3px 8px rgba(0,0,0,0.08); overflow: hidden; transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .pet-card:hover { transform: translateY(-5px); box-shadow: 0 5px 14px rgba(0,0,0,0.15); }
    .pet-photo { width: 100%; height: 180px; object-fit: cover; background-color: #f0f0f0; }
    .pet-info { padding: 15px; }
    .pet-info h3 { margin: 0; color: #e2725b; font-family: 'Parkinsans', sans-serif; font-size: 1.2em; }
    .pet-info p { margin: 6px 0; color: #555; font-size: 0.9em; line-height: 1.4; }
    .empty { text-align: center; color: #777; margin: 40px 0; font-size: 1em; }
    footer { text-align: center; font-size: 0.85em; color: #888; margin-top: 50px; padding: 20px; }
    .msg-sucesso { background-color: #e6ffed; color: #2e7d32; padding: 10px; border-radius: 6px; text-align: center; margin-bottom: 20px; font-weight: bold; }
  </style>
</head>
<body>
  <header>
    <nav>
      <img src="https://images.vexels.com/media/users/3/202255/isolated/preview/a095b3fe28f3c9febbf176089e2d7e08-pegada-de-cachorro-com-osso-de-coracao-rosa-plana.png" alt="Ícone">
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
      <?php if ($res->num_rows === 0): ?>
        <p class="empty">Você ainda não cadastrou nenhum pet 😺 <a href="cadastroPets.html">Cadastrar agora</a></p>
      <?php else: ?>
        <?php while ($pet = $res->fetch_assoc()): ?>
          <div class="pet-card">
            <img src="<?= $pet['foto'] ? htmlspecialchars($pet['foto']) : 'https://placehold.co/400x300?text=Pet' ?>" class="pet-photo" alt="Foto do pet">
            <div class="pet-info">
              <h3><?= htmlspecialchars($pet['nome']) ?></h3>
              <p><strong>Espécie:</strong> <?= htmlspecialchars($pet['especie']) ?></p>
              <?php if ($pet['raca']): ?><p><strong>Raça:</strong> <?= htmlspecialchars($pet['raca']) ?></p><?php endif; ?>
              <p><strong>Idade:</strong> <?= (int)$pet['idade'] ?> anos</p>
              <p><strong>Porte:</strong> <?= htmlspecialchars($pet['porte']) ?></p>
              <?php if ($pet['observacoes']): ?><p><strong>Obs:</strong> <?= nl2br(htmlspecialchars($pet['observacoes'])) ?></p><?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>

  <footer>© 2025 Patas Pousada • Todos os direitos reservados</footer>
</body>
</html>
