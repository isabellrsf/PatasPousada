<?php
// cadastroPets.php
session_start();

// Se você já tem um auth.php que popula a sessão, pode incluir aqui:
// require __DIR__ . '/auth.php';

$nome    = $_SESSION['full_name']  ?? 'Usuário';
$ownerId = $_SESSION['profile_id'] ?? ''; // precisa ter sido salvo no login/registro
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cadastro de Pets</title>

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Parkinsans:wght@400;700&display=swap');
    body { font-family: 'Nunito', sans-serif; background:#f7f7f7; margin:0; }
    header { display:flex; justify-content:center; background:#fff; padding:20px 0; box-shadow:0 2px 4px rgba(0,0,0,.1); }
    header img { width:30px; height:30px; }
    nav { display:flex; justify-content:space-between; align-items:center; max-width:1200px; width:100%; margin:0 auto; padding:0 20px; }
    nav .logo { font-size:1.5em; font-weight:bold; color:#e2725b; text-decoration:none; font-family:'Parkinsans', sans-serif; }
    nav ul { list-style:none; display:flex; gap:15px; }
    nav ul li a { text-decoration:none; color:#333; font-weight:bold; font-family:'Parkinsans', sans-serif; transition:.3s; }
    nav ul li a:hover{ color:red; }
    .container { margin:20px auto; background:#fff; padding:20px; border-radius:8px; width:320px; box-shadow:0 0 10px rgba(0,0,0,.1); }
    h2 { text-align:center; margin-bottom:20px; color:#e2725b; font-family:'Parkinsans', sans-serif; }
    label { display:block; margin-bottom:8px; }
    input[type="text"], input[type="number"], input[type="file"], select, textarea {
      width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;
    }
    textarea { resize:none; height:70px; }
    .radio-group{ margin-bottom:15px; }
    .radio-group label{ margin-right:10px; }
    .preview{ text-align:center; margin-bottom:15px; }
    .preview img{ width:100px; height:100px; object-fit:cover; border-radius:50%; border:2px solid #e2725b; display:none; }
    .main-btn { width:80%; border:2px solid #e2725b; border-radius:20px; display:flex; justify-content:center; margin:auto; padding:10px; background:#e2725b; color:#fff; cursor:pointer; font-size:16px; margin-top:10px; transition:.3s; font-family:'Parkinsans', sans-serif; }
    .main-btn:hover{ background:red; }
    .add-pet{ background:#fff; color:#e2725b; border:2px solid #e2725b; }
    .add-pet:hover{ background:#ffe6e6; }
    .remove-pet-btn{ background:none; border:none; color:#e2725b; font-size:18px; cursor:pointer; margin-left:5px; transition:.2s; vertical-align:middle; }
    .remove-pet-btn:hover{ color:red; transform:scale(1.2); }
    .pet-card{ border:1px solid #eee; padding:15px; border-radius:8px; background:#fafafa; box-shadow:0 1px 3px rgba(0,0,0,.08); margin-bottom:15px; transition:.3s; }
    .pet-card:hover{ transform:translateY(-4px); box-shadow:0 4px 12px rgba(0,0,0,.15); }
    .pet-header{ display:flex; justify-content:center; align-items:center; gap:6px; margin-bottom:10px; }
    .alerta-sucesso{ display:none; background:#ffe6e6; color:#cc0000; border:1px solid #e2725b; border-radius:8px; padding:12px; margin:20px auto; text-align:center; font-weight:bold; width:320px; opacity:0; transition:opacity .8s; }
    .welcome{ text-align:center; margin-top:10px; color:#555; font-family:'Nunito', sans-serif; }
  </style>
</head>
<body>
  <header>
    <nav>
      <img src="https://images.vexels.com/media/users/3/202255/isolated/preview/a095b3fe28f3c9febbf176089e2d7e08-pegada-de-cachorro-com-osso-de-coracao-rosa-plana.png" alt="Ícone">
      <a href="index.html" class="logo">Patas Pousada</a>
      <ul>
        <li><a href="home_tutor.html">Início</a></li>
        <li><a href="meusPets.php">Meus Pets</a></li>
        <li><a href="logout.php">Sair</a></li>
      </ul>
    </nav>
  </header>

  <div class="welcome">
    <p>Olá, <strong><?= htmlspecialchars($nome) ?></strong>! 😊<br>Cadastre aqui seus companheiros de estimação 🐾</p>
  </div>

  <div id="mensagem-sucesso" class="alerta-sucesso"></div>

  <div class="container">
    <h2>Cadastro de Pets</h2>

    <form id="formPets" action="salvarPets.php" method="POST" enctype="multipart/form-data">
      <!-- Dono do pet (vem da sessão) -->
      <input type="hidden" name="owner_id" value="<?= htmlspecialchars($ownerId) ?>">

      <div id="form-container"></div>

      <button type="button" class="main-btn add-pet" onclick="adicionarPet()">+ Adicionar outro pet</button>
      <button type="submit" class="main-btn" id="salvar">Salvar Pets</button>
    </form>

    <div class="criar">
      <a href="meusPets.php">Voltar</a>
    </div>
  </div>

  <script>
    const params = new URLSearchParams(window.location.search);
    if (params.has("sucesso")) {
      const msg = decodeURIComponent(params.get("sucesso"));
      const div = document.getElementById("mensagem-sucesso");
      div.textContent = msg;
      div.style.display = "block";
      setTimeout(() => (div.style.opacity = "1"), 100);
      setTimeout(() => (div.style.opacity = "0"), 5000);
    }

    let contadorPets = 0;
    function adicionarPet() {
      contadorPets++;
      const container = document.getElementById("form-container");
      const petDiv = document.createElement("div");
      petDiv.classList.add("pet-card");
      petDiv.setAttribute("id", `pet_${contadorPets}`);

      const temBotaoRemover = contadorPets > 1
        ? `<button type="button" class="remove-pet-btn" onclick="removerPet(${contadorPets})" title="Remover este pet">✕</button>`
        : "";

      petDiv.innerHTML = `
        <div class="pet-header">
          <h4 style="text-align:center; color:#e2725b; margin:0;">🐶 Pet ${contadorPets}</h4>
          ${temBotaoRemover}
        </div>

        <label>Nome do Pet:</label>
        <input type="text" name="nome_pet_${contadorPets}" required placeholder="Ex: Thor">

        <label>Espécie:</label>
        <select name="especie_pet_${contadorPets}" required onchange="mostrarOutro(this, ${contadorPets})">
          <option value="">Selecione</option>
          <option value="Cachorro">Cachorro</option>
          <option value="Gato">Gato</option>
          <option value="Pássaro">Pássaro</option>
          <option value="Roedor">Roedor</option>
          <option value="Réptil">Réptil</option>
          <option value="Outro">Outro</option>
        </select>

        <input type="text" id="outro_${contadorPets}" name="outro_especie_${contadorPets}" placeholder="Especifique a espécie" style="display:none;">

        <label>Raça:</label>
        <input type="text" name="raca_pet_${contadorPets}" placeholder="Ex: Poodle, Siamês...">

        <div class="radio-group">
          <label>Sexo:</label>
          <label><input type="radio" name="sexo_pet_${contadorPets}" value="Macho" required> Macho</label>
          <label><input type="radio" name="sexo_pet_${contadorPets}" value="Fêmea"> Fêmea</label>
        </div>

        <label>Idade (anos):</label>
        <input type="number" name="idade_pet_${contadorPets}" min="0" max="40" required placeholder="Ex: 3">

        <label>Porte:</label>
        <select name="porte_pet_${contadorPets}" required>
          <option value="">Selecione</option>
          <option value="Pequeno">Pequeno</option>
          <option value="Médio">Médio</option>
          <option value="Grande">Grande</option>
        </select>

        <label>Observações:</label>
        <textarea name="obs_pet_${contadorPets}" placeholder="Ex: Toma remédio, é sociável, etc."></textarea>

        <label>Foto do Pet:</label>
        <input type="file" name="foto_pet_${contadorPets}" accept="image/*" onchange="previewImagem(event, ${contadorPets})">
        <div class="preview"><img id="preview_${contadorPets}" alt="Preview do pet"></div>
      `;
      container.appendChild(petDiv);
    }

    function mostrarOutro(select, id) {
      const outro = document.getElementById(`outro_${id}`);
      outro.style.display = select.value === "Outro" ? "block" : "none";
    }

    function previewImagem(event, id) {
      const preview = document.getElementById(`preview_${id}`);
      const arquivo = event.target.files[0];
      if (arquivo) {
        const leitor = new FileReader();
        leitor.onload = () => {
          preview.src = leitor.result;
          preview.style.display = "block";
        };
        leitor.readAsDataURL(arquivo);
      } else {
        preview.style.display = "none";
      }
    }

    function removerPet(id) {
      const petCard = document.getElementById(`pet_${id}`);
      petCard.style.opacity = '0';
      setTimeout(() => petCard.remove(), 300);
    }

    // Gera o 1º bloco ao carregar a página
    document.addEventListener("DOMContentLoaded", () => adicionarPet());
  </script>
</body>
</html>
