<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/conexao.php";

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$login  = isset($data['cnpj']) ? trim($data['cnpj']) : '';
$senha  = isset($data['senha']) ? (string)$data['senha'] : '';

if ($login === '' || $senha === '') {
    echo json_encode(['ok' => false, 'msg' => 'Informe CNPJ/CPF e Senha.']);
    exit;
}

// Remove caracteres não numéricos (apenas se for CPF/CNPJ)
$loginDigits = preg_replace('/\D+/', '', $login);

// === 1. Tenta autenticar como Cliente (usa CPF) ===
try {
    $sql = "SELECT idCliente, nome FROM Cliente WHERE cpf = :cpf AND senha = :senha LIMIT 1";
    $st  = $pdo->prepare($sql);
    $st->bindValue(':cpf', $loginDigits);
    $st->bindValue(':senha', $senha);
    $st->execute();

    if ($cli = $st->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['auth']      = true;
        $_SESSION['user_type'] = 'cliente';
        $_SESSION['user_id']   = (int)$cli['idCliente'];
        $_SESSION['nome']      = $cli['nome'];
        echo json_encode(['ok' => true, 'redirect' => '../paginas/home.html']);
        exit;
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => 'Erro ao verificar cliente.']);
    exit;
}

// === 2. Tenta autenticar como Empresa ===
try {
    $sql = "SELECT idEmpresa, nome_fantasia FROM Empresa
            WHERE (usuario = :login OR cnpj = :login) AND senha = :senha LIMIT 1";
    $st  = $pdo->prepare($sql);
    $st->bindValue(':login', $login);
    $st->bindValue(':senha', $senha);
    $st->execute();

    if ($emp = $st->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['auth']      = true;
        $_SESSION['user_type'] = 'empresa';
        $_SESSION['user_id']   = (int)$emp['idEmpresa'];
        $_SESSION['nome']      = $emp['nome_fantasia'];
        echo json_encode(['ok' => true, 'redirect' => '../PAGINASLOGISTA/home_logista.html']);
        exit;
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => 'Erro ao verificar empresa.']);
    exit;
}

// === 3. Falha geral ===
echo json_encode(['ok' => false, 'msg' => 'Credenciais inválidas.']);
