<?php
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = [];

// Destruir a sessão
session_destroy();

// Redirecionar para o login
header("Location: login.html");
exit();
?>
