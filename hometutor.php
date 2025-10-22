<?php
require __DIR__ . '/auth.php';
$nome = $_SESSION['full_name'] ?? 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Home Tutor</title></head>
<body>
  <h2>Bem-vindo(a), <?= htmlspecialchars($nome) ?>!</h2>
  <p>Gerencie seus pets e dados</p>
  <p>
    <a href="meusPets.php">Ver Meus Pets</a> |
    <a href="cadastroPets.html">Cadastrar Novo Pet</a> |
    <a href="logout.php">Sair</a>
  </p>
</body>
</html>
