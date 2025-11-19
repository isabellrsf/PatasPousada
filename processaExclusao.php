<?php
// processaExclusao.php
session_start();
require __DIR__ . '/supabase.php';

// 1. Identifica quem está logado (serve para Host ou Tutor)
$user_id = $_SESSION['host_id'] ?? $_SESSION['profile_id'] ?? null;

if (!$user_id) {
    // Se não tiver ninguém logado, manda para o login
    header("Location: cadastrarouentrar.html");
    exit;
}

/* 2. Chama a função de deletar que JÁ EXISTE no seu supabase.php
   Essa função usa a Service Role Key (admin) para apagar o usuário do Auth.
*/
list($status, $response) = sb_auth_admin_delete_user($user_id);

// 3. Verifica o resultado
if ($status >= 200 && $status < 300) {
    // SUCESSO: Usuário apagado no Supabase.
    
    // Destrói a sessão do navegador (desloga)
    session_unset();
    session_destroy();

    // Redireciona para a página inicial com mensagem
    header("Location: index.html?msg=" . urlencode("Sua conta foi excluída com sucesso."));
    exit;
} else {
    // ERRO
    $msgErro = $response['msg'] ?? 'Erro desconhecido ao tentar excluir.';
    
    // Volta para a página anterior (Home do Host) com o erro
    header("Location: home_host.php?erro=" . urlencode("Falha ao excluir: " . $msgErro));
    exit;
}
?>