<?php
session_start();


if (!isset($_SESSION['nome'])) {
    header("Location: login.html");
    exit();
}

$nomeUsuario = $_SESSION['nome'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Painel do Usuário</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Parkinsans:wght@300..800&display=swap');
    
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f7f7;
      margin: 10px;
    }

    header {
      justify-content: center;
      display: flex;
      background-color: #fff;
      padding: 20px 0;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    header img {
      width: 30px;
      height: 30px;
    }

    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    nav .logo {
      font-size: 1.5em;
      font-weight: bold;
      color: #e2725b;
      text-decoration: none;
      font-family: 'Parkinsans', sans-serif;
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 15px;
    }

    nav ul li a {
      text-decoration: none;
      color: #333;
      font-weight: bold;
      font-family: 'Parkinsans', sans-serif;
    }

    .container {
      margin: 40px auto;
      background-color: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 90%;
      max-width: 700px;
      text-align: center;
    }

    h1 {
      color: #e2725b;
      margin-bottom: 10px;
    }

    p {
      font-size: 1.1em;
      color: #333;
    }

    .btns {
      margin-top: 30px;
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btns a {
      background-color: #e2725b;
      color: white;
      padding: 10px 20px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: bold;
      transition: 0.3s;
    }

    .btns a:hover {
      background-color: #c65038;
    }
  </style>
</head>
<body>

  <header>
    <nav>
      <img src="https://images.vexels.com/media/users/3/202255/isolated/preview/a095b3fe28f3c9febbf176089e2d7e08-pegada-de-cachorro-com-osso-de-coracao-rosa-plana.png" alt="Ícone">
      <a href="home.html" class="logo">Patas Pousada</a>
      <ul>
        <li><a href="home.html">Início</a></li>
        <li><a href="ajuda.html">Ajuda</a></li>
      </ul>
    </nav>
  </header>

  <div class="container">
    <h1>Olá, <?php echo htmlspecialchars($nomeUsuario); ?>! 🐾</h1>
    <p>Seja bem-vindo(a) ao seu painel. Aqui você poderá acessar suas reservas e gerenciar seus dados.</p>

    <div class="btns">
      <a href="#">Minhas Reservas</a>
      <a href="#">Editar Perfil</a>
      <a href="logout.php">Sair</a>
    </div>
  </div>

</body>
</html>
