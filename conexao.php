<?php
$servername = "localhost";
$username = "root";
$password = ""; // sem senha, padrão do XAMPP
$database = "pataspousada";

// Cria conexão
$conn = new mysqli($servername, $username, $password, $database);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
