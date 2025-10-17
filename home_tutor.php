<?php
require 'auth.php';
require 'conexao.php';
$nome_tutor = $_SESSION['nome_tutor'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home Tutor - Patas Pousada</title>
<style>
body {
  font-family: 'Nunito', sans-serif;
  background-color: #f7f7f7;
  margin: 0;
}
.container {
  max-width: 500px;
  margin: 60px auto;
  background: #fff;
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  text-align: center;
}
h2 {
  color: #e2725b;
  font-family: 'Parkinsans', sans-serif;
}
a {
  display: block;
  margin: 12px auto;
  padding: 10px 15px;
  border: 2px solid #e2725b;
  border-radius: 20px;
  text-decoration: none;
  color: #e2725b;
  font-weight: bold;
  width: 70%;
  transition: 0.3s;
}
a:hover {
  background-color: #e2725b;
  color: white;
}
</style>
</head>
<body>
  <div class="container">
    <h2>🐾 Bem-vindo(a), <?= htmlspecialchars($nome_tutor) ?>!</h2>
    <p>Gerencie seus pets e dados na Patas Pousada.</p>
    <a href="meusPets.php">Ver Meus Pets</a>
    <a href="cadastroPets.html">Cadastrar Novo Pet</a>
    <a href="logout.php">Sair</a>
  </div>
</body>
</html>
