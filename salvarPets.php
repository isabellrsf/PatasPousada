<?php
session_start();
require 'auth.php';
require 'conexao.php';

// ID do tutor logado
$tutor_id = (int)$_SESSION['id_tutor'];

// Conta quantos pets foram enviados
$contador = 0;
foreach ($_POST as $key => $value) {
    if (strpos($key, 'nome_pet_') === 0) {
        $contador++;
    }
}

// Nenhum pet?
if ($contador === 0) {
    echo "<script>alert('Nenhum pet foi adicionado.'); window.location='cadastroPets.html';</script>";
    exit();
}

// Cria pasta de uploads se não existir
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Loop para salvar cada pet
for ($i = 1; $i <= $contador; $i++) {
    $nome = $_POST["nome_pet_$i"] ?? '';
    $especie = $_POST["especie_pet_$i"] ?? '';
    $outro_especie = $_POST["outro_especie_$i"] ?? null;
    $raca = $_POST["raca_pet_$i"] ?? null;
    $sexo = $_POST["sexo_pet_$i"] ?? '';
    $idade = $_POST["idade_pet_$i"] ?? 0;
    $porte = $_POST["porte_pet_$i"] ?? '';
    $obs = $_POST["obs_pet_$i"] ?? null;

    // Foto
    $foto_path = null;
    $fotoKey = "foto_pet_$i";
    if (!empty($_FILES[$fotoKey]['name'])) {
        $nomeFoto = time() . "_" . basename($_FILES[$fotoKey]['name']);
        $destino = $uploadDir . $nomeFoto;
        if (move_uploaded_file($_FILES[$fotoKey]['tmp_name'], $destino)) {
            $foto_path = $destino;
        }
    }

    // Insere no banco usando tutor_id (igual ao seu banco)
    $stmt = $conn->prepare("INSERT INTO pets 
        (tutor_id, nome, especie, outro_especie, raca, sexo, idade, porte, observacoes, foto)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssss", 
        $tutor_id, $nome, $especie, $outro_especie, $raca, $sexo, $idade, $porte, $obs, $foto_path
    );
    $stmt->execute();
}

$stmt->close();
$conn->close();

// Redireciona para a página de pets
header("Location: meusPets.php?sucesso=" . urlencode("Pets cadastrados com sucesso!"));
exit();
?>
