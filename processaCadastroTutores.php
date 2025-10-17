<?php
// processaCadastroTutores.php
session_start();
require 'conexao.php';

// 1) coletar dados
$nome            = trim($_POST['name'] ?? '');
$cpf             = trim($_POST['cpf'] ?? '');
$birth_date_raw  = $_POST['birth_date'] ?? ''; // YYYY-MM-DD
$email           = trim($_POST['email'] ?? '');
$senha           = $_POST['password'] ?? '';
$confirm_senha   = $_POST['confirm_password'] ?? '';
$tipos           = $_POST['pet_type'] ?? [];   // checkboxes (se existirem)
$especificacao   = trim($_POST['other_pet'] ?? '');

// 2) validações básicas
if ($senha !== $confirm_senha) {
  header("Location: registrotutores.html?erro=As+senhas+n%C3%A3o+coincidem");
  exit();
}
if (!$nome || !$cpf || !$birth_date_raw || !$email || !$senha) {
  header("Location: registrotutores.html?erro=Preencha+todos+os+campos+obrigat%C3%B3rios");
  exit();
}

// 3) calcular idade no servidor (18 a 100)
$birth_date = date('Y-m-d', strtotime($birth_date_raw));
$hoje = new DateTime();
$nasc = new DateTime($birth_date);
$idade = $hoje->diff($nasc)->y;
if ($idade < 18 || $idade > 100) {
  header("Location: registrotutores.html?erro=Idade+inv%C3%A1lida%3A+permitido+entre+18+e+100+anos");
  exit();
}

// 4) checar duplicados (email e cpf)
$stmt = $conn->prepare("SELECT id FROM tutores WHERE email = ? OR cpf = ?");
$stmt->bind_param("ss", $email, $cpf);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  header("Location: registrotutores.html?erro=E-mail+ou+CPF+j%C3%A1+est%C3%A3o+cadastrados");
  exit();
}
$stmt->close();

// 5) hash de senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// 6) preparar campos opcionais
$tipo_pet_str = is_array($tipos) ? implode(',', $tipos) : null;
$especificacao = $especificacao ?: null;

// 7) inserir
$sql = "INSERT INTO tutores (nome, cpf, birth_date, idade, email, senha, tipo_pet, especificacao_pet)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssissss", $nome, $cpf, $birth_date, $idade, $email, $senha_hash, $tipo_pet_str, $especificacao);

if ($stmt->execute()) {
  $id = $stmt->insert_id;
  // abrir sessão do tutor
  $_SESSION['id_tutor']  = $id;
  $_SESSION['nome_tutor'] = $nome;

  // redirecionar para cadastro de pets (obrigatório)
  header("Location: cadastroPets.html?sucesso=Cadastro+conclu%C3%ADdo%21+Agora+cadastre+seus+pets.");
  exit();
} else {
  header("Location: registrotutores.html?erro=Erro+ao+salvar+no+banco");
  exit();
}
