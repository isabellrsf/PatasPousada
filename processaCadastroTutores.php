<?php
ob_start(); // Inicia o buffer de saída
$servername = "localhost";
$username = "root";
$password = "Ceub123456";
$database = "PatasPousada";

// Conectar ao banco
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Capturar dados do formulário
$nome = $_POST['name'];
$cpf = $_POST['cpf'];
$idade = $_POST['age'];
$email = $_POST['email'];
$senha = $_POST['password'];
$confirm_senha = $_POST['confirm_password'];
$quantidade_pets = $_POST['pets'];
$tipo_pet = $_POST['pet_type'];
$especificacao_pet = isset($_POST['other_pet']) ? $_POST['other_pet'] : null;

// Validar senhas
if ($senha !== $confirm_senha) {
    header("Location: registrotutores.html?erro=As+senhas+n%C3%A3o+coincidem.");
    exit();
}

// Verificar se e-mail já existe
$checkEmail = $conn->prepare("SELECT id FROM tutores WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    header("Location: registrotutores.html?erro=Este+e-mail+j%C3%A1+est%C3%A1+cadastrado.");
    exit();
}

// Verificar se CPF já existe
$checkCpf = $conn->prepare("SELECT id FROM tutores WHERE cpf = ?");
$checkCpf->bind_param("s", $cpf);
$checkCpf->execute();
$checkCpf->store_result();

if ($checkCpf->num_rows > 0) {
    header("Location: registrotutores.html?erro=Este+CPF+j%C3%A1+est%C3%A1+cadastrado.");
    exit();
}

// Criptografar senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Inserir dados
$sql = "INSERT INTO tutores (nome, cpf, idade, email, senha, quantidade_pets, tipo_pet, especificacao_pet)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssississ", $nome, $cpf, $idade, $email, $senha_hash, $quantidade_pets, $tipo_pet, $especificacao_pet);

if ($stmt->execute()) {
    header("Location: sucesso.html");
    exit();
} else {
    header("Location: registrotutores.html?erro=Erro+ao+salvar+no+banco.");
    exit();
}

$conn->close();
?>
