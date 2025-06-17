<?php
session_start();

// Conexão com o banco
$servername = "localhost";
$username = "root";
$password = "Ceub123456"; // ou "" se seu MySQL não tiver senha
$database = "PatasPousada";

$conn = new mysqli($servername, $username, $password, $database);

// Verifica conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Captura os dados do formulário
$email = $_POST['email'];
$senha = $_POST['password'];

// Verifica se o e-mail existe em tutores
$sql = "SELECT id, nome, senha FROM tutores WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Se não achou em tutores, tenta anfitriões
if ($result->num_rows === 0) {
    $sql2 = "SELECT id, nome, senha FROM anfitrioes WHERE email = ?";
    $stmt = $conn->prepare($sql2);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Se achou em algum dos dois
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if (password_verify($senha, $user['senha'])) {
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['id'] = $user['id'];
        header("Location: painel.php");
        exit();
    } else {
        header("Location: login.html?erro=Senha+incorreta");
        exit();
    }
} else {
    header("Location: login.html?erro=E-mail+n%C3%A3o+encontrado");
    exit();
}

$conn->close();
?>
