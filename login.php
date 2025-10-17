<?php
session_start();
require 'conexao.php';

// Captura dados do formulário
$email = $_POST['email'];
$senha = $_POST['password'];

// Busca o tutor no banco
$sql = "SELECT id, nome, senha FROM tutores WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Se encontrou o tutor
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($senha, $user['senha'])) {
        $_SESSION['id_tutor'] = $user['id'];
        $_SESSION['nome_tutor'] = $user['nome'];

        header("Location: home_tutor.html");
        exit();
    } else {
        echo "<script>alert('Senha incorreta!'); window.location='login.html';</script>";
    }
} else {
    echo "<script>alert('E-mail não encontrado!'); window.location='login.html';</script>";
}
?>
