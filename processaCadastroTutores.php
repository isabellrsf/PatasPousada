<?php
// processaCadastroTutores.php
session_start();
require __DIR__ . '/supabase.php';

// Função de redirecionamento com erro
function redirectWithError(string $msg): void {
    // IMPORTANTE: Confirme se o nome do seu HTML é este mesmo
    header('Location: registrotutores.html?erro=' . urlencode($msg));
    exit;
}

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('Método inválido.');
}

// --- 1. COLETA DE DADOS ---
$nome  = trim($_POST['name'] ?? '');
$cpf   = preg_replace('/\D+/', '', $_POST['cpf'] ?? ''); // Remove pontos e traços
$data  = $_POST['birth_date'] ?? '';
$email = trim($_POST['email'] ?? '');
$senha = $_POST['password'] ?? '';
$conf  = $_POST['confirm_password'] ?? '';

// Lógica dos Pets (soma total para salvar no banco)
$totalPets = 0;
// Pega os tipos marcados
$tipos = $_POST['pet_type'] ?? [];
if (is_array($tipos)) {
    foreach ($tipos as $tipo) {
        $qtdName = ($tipo === 'outro') ? 'quantidade_outro' : "quantidade_{$tipo}";
        $qtd = (int)($_POST[$qtdName] ?? 0);
        if ($qtd > 0) {
            $totalPets += $qtd;
        }
    }
}

// --- 2. VALIDAÇÕES ---
if (!$nome || !$cpf || !$data || !$email || !$senha) {
    redirectWithError('Preencha todos os campos obrigatórios.');
}

if ($senha !== $conf) {
    redirectWithError('As senhas não coincidem.');
}

if (strlen($senha) < 6) {
    redirectWithError('A senha deve ter pelo menos 6 caracteres.');
}

// Validação de Idade (PHP)
try {
    $dtNasc = new DateTime($data);
    $hoje   = new DateTime();
    $idade  = $hoje->diff($dtNasc)->y;
    if ($idade < 18 || $idade > 100) {
        redirectWithError('Idade inválida (18 a 100 anos).');
    }
} catch (Exception $e) {
    redirectWithError('Data de nascimento inválida.');
}

// --- 3. ETAPA 1: CRIAR NO AUTH (SUPABASE) ---
// Passamos metadados para que fiquem salvos no JSON do usuário também
$metadata = [
    'full_name' => $nome,
    'role'      => 'tutor',
    'cpf'       => $cpf
];

list($authStatus, $authBody) = sb_auth_admin_create_user($email, $senha, $metadata);

if ($authStatus >= 400) {
    $msgErro = $authBody['msg'] ?? $authBody['message'] ?? 'Erro desconhecido.';
    if (strpos($msgErro, 'already registered') !== false) {
        redirectWithError('Este e-mail já está cadastrado.');
    }
    redirectWithError('Erro no cadastro: ' . $msgErro);
}

$user_id = $authBody['id'] ?? null;
if (!$user_id) {
    redirectWithError('Erro inesperado: Usuário criado sem ID.');
}

// --- 4. ETAPA 2: INSERIR NA TABELA PUBLIC.PROFILES ---
// Como desligamos a trigger, precisamos inserir aqui manualmente

$payloadProfile = [
    'id'             => $user_id,    // Vincula com Auth
    'full_name'      => $nome,
    'cpf'            => $cpf,
    'birth_date'     => $data,
    'email'          => $email,
    'role'           => 'tutor',     // Campo importante para diferenciar
    'pets_count'     => $totalPets,  // Salvamos o total calculado
    // Adicione outros campos se sua tabela tiver (ex: city, phone)
];

list($status, $resBody) = sb_request(
    'POST',
    '/rest/v1/profiles',
    $payloadProfile
);

// Se falhar no banco de dados, desfazemos o Auth (Rollback)
if ($status >= 300) {
    sb_auth_admin_delete_user($user_id);
    
    $msgDb = $resBody['message'] ?? 'Erro ao salvar perfil.';
    if (strpos($msgDb, 'duplicate key') !== false) {
        redirectWithError('Dados duplicados (CPF ou E-mail já existem).');
    }
    redirectWithError('Erro ao salvar dados do perfil: ' . $msgDb);
}

// --- 5. SUCESSO ---
$_SESSION['profile_id'] = $user_id;
$_SESSION['full_name']  = $nome;
$_SESSION['role']       = 'tutor';

// Redireciona para a Home do Tutor
header("Location: home_tutor.php");
exit;