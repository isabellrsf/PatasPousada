<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/* Após login, vamos setar:
   $_SESSION['profile_id']  (UUID do profiles.id)
   $_SESSION['full_name']   (nome)
*/

// ⚠️ Modo de teste — REMOVA depois que o login real estiver ok
if (!isset($_SESSION['profile_id'])) {
  $_SESSION['profile_id'] = 'COLOQUE_UM_UUID_VALIDO_DE_TESTE_AQUI';
  $_SESSION['full_name']  = 'Usuário Teste';
}
