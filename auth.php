<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 Se não existir sessão de tutor, cria um ID temporário (modo de teste)
if (!isset($_SESSION['id_tutor'])) {
    // ⚠️ enquanto não houver login real, isso serve para simular um tutor logado
    $_SESSION['id_tutor'] = 1;
}
?>
