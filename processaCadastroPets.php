<?php
session_start();
require 'auth.php';
require 'conexao.php';

$id_tutor = (int)$_SESSION['id_tutor'];

$nome = $_POST['nome'];
$especie = $_POST['especie'];
$outro_especie = $_POST['outro_especie'] ?? null;
$raca = $_POST['raca'] ?? null;
$sexo = $_POST['sexo'];
$idade = $_POST['idade'];
$porte = $_POST['porte'];
$observacoes = $_POST['observacoes'] ?? null;

// Upload da foto (opcional)
$foto_path = null;
if (!empty($_FILES['foto']['name'])) {
    $destino = "uploads/" . basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], $destino);
    $foto_path = $destino;
}

$stmt = $conn->prepare("INSERT INTO pets (id_tutor, nome, especie, outro_especie, raca, sexo, idade, porte, observacoes, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssssss", $id_tutor, $nome, $especie, $outro_especie, $raca, $sexo, $idade, $porte, $observacoes, $foto_path);

if ($stmt->execute()) {
    header("Location: meusPets.php?sucesso=" . urlencode("Pet cadastrado com sucesso!"));
    exit();
} else {
    echo "Erro ao cadastrar pet: " . $stmt->error;
}
?>
