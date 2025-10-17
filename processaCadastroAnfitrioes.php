<?php
ob_start(); // Inicia o buffer de saída

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "ceub123456";
$database = "PatasPousada";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Pegar os dados do formulário
$nome = $_POST['name'];
$cpf = $_POST['cpf'];
$idade = $_POST['age'];
$email = $_POST['email'];
$senha = $_POST['password'];
$confirm_senha = $_POST['confirm_password'];
$cidade = $_POST['city'];
$quantidade_pets = $_POST['pets'];
$residencia = $_POST['residence'];

// Validar se senhas são iguais
if ($senha !== $confirm_senha) {
    header("Location: registroaft.html?erro=As+senhas+n%C3%A3o+coincidem.");
    exit();
}

// Verificar se o e-mail já está cadastrado
$checkEmail = $conn->prepare("SELECT id FROM anfitrioes WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    header("Location: registroaft.html?erro=Este+e-mail+j%C3%A1+est%C3%A1+cadastrado.");
    exit();
}

// Verificar se o CPF já está cadastrado
$checkCpf = $conn->prepare("SELECT id FROM anfitrioes WHERE cpf = ?");
$checkCpf->bind_param("s", $cpf);
$checkCpf->execute();
$checkCpf->store_result();

if ($checkCpf->num_rows > 0) {
    header("Location: registroaft.html?erro=Este+CPF+j%C3%A1+est%C3%A1+cadastrado.");
    exit();
}

// Criptografar senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Inserir os dados
$sql = "INSERT INTO anfitrioes (nome, cpf, idade, email, senha, cidade, quantidade_pets, residencia)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssisssis", $nome, $cpf, $idade, $email, $senha_hash, $cidade, $quantidade_pets, $residencia);

if ($stmt->execute()) {
    header("Location: sucesso.html");
    exit();
} else {
    header("Location: registroaft.html?erro=Erro+ao+salvar+no+banco.");
    exit();
}

$conn->close();
?>
