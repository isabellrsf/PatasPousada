<?php 
session_start();
require __DIR__ . '/supabase.php';

/**
 * Redireciona de volta para o formulário com uma mensagem de erro
 */
function redirectWithError(string $msg): void {
    // IMPORTANTE: Verifique se este nome é EXATAMENTE o do seu arquivo HTML
    $paginaCadastro = 'registroaft.html'; 
    header('Location: ' . $paginaCadastro . '?erro=' . urlencode($msg));
    exit;
}

// Garante que só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('Método de requisição inválido.');
}

// Coleta dados vindos do formulário
$nome        = trim($_POST['name'] ?? '');
$cpf         = trim($_POST['cpf'] ?? '');
$data        = $_POST['birth_date'] ?? '';
$email       = trim($_POST['email'] ?? '');
$senha       = $_POST['password'] ?? '';
$conf        = $_POST['confirm_password'] ?? '';
$city        = trim($_POST['city'] ?? '');
$pets_count  = isset($_POST['pets']) ? (int) $_POST['pets'] : 0;
$residence   = trim($_POST['residence'] ?? '');

// --- VALIDAÇÕES BÁSICAS ---

// 1. Campos vazios
if (!$nome || !$cpf || !$data || !$email || !$senha || !$city || !$residence) {
    redirectWithError('Preencha todos os campos obrigatórios.');
}

// 2. Senhas iguais
if ($senha !== $conf) {
    redirectWithError('As senhas não coincidem.');
}

// 3. Tamanho da senha (O Supabase exige no mínimo 6)
if (strlen($senha) < 6) {
    redirectWithError('A senha deve ter pelo menos 6 caracteres.');
}

// 4. Validação de idade
$hoje = new DateTime();
try {
    $dtNasc = new DateTime($data);
    $idade = $hoje->diff($dtNasc)->y;

    if ($idade < 18 || $idade > 100) {
        redirectWithError('Idade inválida! É necessário ter entre 18 e 100 anos.');
    }
} catch (Exception $e) {
    redirectWithError('Data de nascimento inválida.');
}

/* ------------------------------------------------------------------
   ETAPA 1 — CRIAR USUÁRIO NO SUPABASE AUTH (Admin API)
   ------------------------------------------------------------------ */

// CORREÇÃO DO ERRO JSON:
// Adicionamos um array associativo ['origem' => 'cadastro_php'] como terceiro parâmetro.
// Isso força o PHP a enviar um Objeto JSON "{}", que é o que o Supabase espera.
list($authStatus, $authBody) = sb_auth_admin_create_user($email, $senha, ['origem' => 'cadastro_php']);

// Se der erro (código 400 ou maior), pegamos a mensagem real do Supabase
if ($authStatus >= 400) {
    $msgErro = $authBody['msg'] ?? $authBody['message'] ?? 'Erro desconhecido ao criar login.';
    
    // Tradução amigável para erros comuns
    if (strpos($msgErro, 'already registered') !== false) {
        $msgErro = 'Este e-mail já está cadastrado.';
    }
    
    redirectWithError('Erro no cadastro: ' . $msgErro);
}

// Pegamos o ID do usuário criado no Auth
$user_id = $authBody['id'] ?? null;

if (!$user_id) {
    redirectWithError('Falha inesperada: Usuário criado sem ID.');
}

/* ------------------------------------------------------------------
   ETAPA 2 — INSERIR OS DADOS NA TABELA HOSTS
   ------------------------------------------------------------------ */

$payload = [
    'id'             => $user_id,   // Vincula com auth.users
    'full_name'      => $nome,
    'cpf'            => $cpf,
    'birth_date'     => $data,
    'email'          => $email,
    'city'           => $city,
    'pets_count'     => $pets_count,
    'residence_type' => $residence,
];

list($status, $resBody) = sb_request(
    'POST',
    '/rest/v1/hosts',
    $payload
);

// Se falhar ao salvar na tabela (ex: CPF duplicado, erro de tipo), desfazemos a conta
if ($status >= 300) {
    
    // ROLLBACK: Apaga o usuário do Auth para não ficar "preso" no sistema
    sb_auth_admin_delete_user($user_id);

    $msgErroDb = $resBody['message'] ?? 'Erro ao salvar perfil.';
    
    // Verifica se é erro de duplicidade (ex: CPF)
    if (strpos($msgErroDb, 'duplicate key') !== false) {
        if (strpos($msgErroDb, 'cpf') !== false) {
            redirectWithError('Este CPF já está cadastrado em outra conta.');
        }
        redirectWithError('Dados duplicados (Email ou CPF já existem).');
    }

    redirectWithError('Erro ao salvar dados: ' . $msgErroDb);
}

// --- SUCESSO ---

// Guarda dados mínimos na sessão
$_SESSION['host_id']   = $user_id;
$_SESSION['host_name'] = $nome;

// Redireciona para HOME HOST
header('Location: home_host.php');
exit;
?>