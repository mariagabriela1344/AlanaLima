<?php
// PHP/login.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Conexão com o banco de dados
require_once __DIR__ . "/conexao.php";

// Recebe dados enviados via JSON ou POST
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

// Corrige nomes vindos do JavaScript
$cpfOrUser = isset($data['cpfCnpj']) ? trim($data['cpfCnpj']) : '';
$senha     = isset($data['senha']) ? (string)$data['senha'] : '';

if ($cpfOrUser === '' || $senha === '') {
    echo json_encode(['ok' => false, 'msg' => 'Informe CPF/CNPJ e senha.']);
    exit;
}

// Remove caracteres não numéricos do CPF/CNPJ
$cpfDigits = preg_replace('/\D+/', '', $cpfOrUser);

// =============================
// 🔹 1. Autenticação de CLIENTE (senha em texto)
# Note: compara diretamente a string armazenada no campo `senha`
// =============================
try {
    $sql = "SELECT idCliente, nome, senha FROM Cliente WHERE cpf = :cpf LIMIT 1";
    $st  = $pdo->prepare($sql);
    $st->bindValue(':cpf', $cpfDigits);
    $st->execute();

    if ($cli = $st->fetch(PDO::FETCH_ASSOC)) {
        // COMPARAÇÃO DIRETA (texto)
        if ($senha === $cli['senha']) {
            $_SESSION['auth']      = true;
            $_SESSION['user_type'] = 'cliente';
            $_SESSION['user_id']   = (int)$cli['idCliente'];
            $_SESSION['nome']      = $cli['nome'];

            echo json_encode(['ok' => true, 'redirect' => 'paginas/home.html']);
            exit;
        }
    }
} catch (Throwable $e) {
    // Não expor detalhes do erro ao cliente
    echo json_encode(['ok' => false, 'msg' => 'Erro ao verificar cliente.']);
    exit;
}

// =============================
// 🔹 2. Autenticação de EMPRESA (senha em texto)
// =============================
try {
    $sql = "SELECT idEmpresa, nome_fantasia, senha 
            FROM Empresa
            WHERE (usuario = :u OR cnpj = :u) LIMIT 1";
    $st  = $pdo->prepare($sql);
    $st->bindValue(':u', $cpfOrUser);
    $st->execute();

    if ($emp = $st->fetch(PDO::FETCH_ASSOC)) {
        if ($senha === $emp['senha']) {
            $_SESSION['auth']      = true;
            $_SESSION['user_type'] = 'empresa';
            $_SESSION['user_id']   = (int)$emp['idEmpresa'];
            $_SESSION['nome']      = $emp['nome_fantasia'];

            echo json_encode(['ok' => true, 'redirect' => 'paginaslogista/home_logista.html']);
            exit;
        }
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => 'Erro ao verificar empresa.']);
    exit;
}

// =============================
// ❌ 3. Falha geral de login
// =============================
echo json_encode(['ok' => false, 'msg' => 'Credenciais inválidas.']);
