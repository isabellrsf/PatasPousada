<?php
session_start();
$_SESSION = [];
session_destroy();

// se quiser, também pode limpar cookie específico de sessão aqui

header('Location: cadastrarouentrar.html');
exit;
