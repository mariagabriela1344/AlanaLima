<?php
// cadastro_cliente.php
// Conectando ao banco de dados
require_once __DIR__ . "/conexao.php";

// Função de redirecionamento
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

// Processa apenas a ação "cadastrar"
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["acao"] ?? '') === "cadastrar") {
    try {
        // Recebe campos com trim para evitar espaços desnecessários
        $nome = trim($_POST["nome"] ?? '');
        $email = trim($_POST["email"] ?? '');
        $senha = trim($_POST["senha"] ?? '');
        $confirmarsenha = trim($_POST["confirmar"] ?? '');
        $telefone = trim($_POST["telefone"] ?? '');
        $cpf = trim($_POST["cpf"] ?? '');

        // Validações
        $erros = [];

        if ($nome === '' || $email === '' || $senha === '' || $confirmarsenha === '' || $telefone === '' || $cpf === '') {
            $erros[] = "Preencha todos os campos.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = "E-mail inválido.";
        }
        if ($senha !== $confirmarsenha) {
            $erros[] = "As senhas não conferem.";
        }
        if (strlen($senha) < 8) {
            $erros[] = "A senha deve ter pelo menos 8 caracteres.";
        }
        // Remova qualquer máscara antes de validar quantidade de dígitos se necessário
        $telefone_numeros = preg_replace('/\D+/', '', $telefone);
        if (strlen($telefone_numeros) < 10) { // 10 ou 11 dependendo do formato esperado
            $erros[] = "Telefone incorreto.";
        }
        $cpf_numeros = preg_replace('/\D+/', '', $cpf);
        if (strlen($cpf_numeros) < 11) {
            $erros[] = "CPF inválido.";
        }

        if ($erros) {
            redirecWith("../paginas/cadastro.html", ["erro" => $erros[0]]);
        }

        // Verificar se CPF já existe
        $stmt = $pdo->prepare("SELECT 1 FROM Cliente WHERE cpf = :cpf LIMIT 1");
        $stmt->execute([':cpf' => $cpf_numeros]);
        if ($stmt->fetchColumn()) {
            redirecWith('../paginas/cadastro.html', ["erro" => "CPF já está cadastrado"]);
        }

        // Inserir cliente (usar password_hash para a senha)
        $sql = "INSERT INTO Cliente (nome, cpf, telefone, email, senha)
                VALUES (:nome, :cpf, :telefone, :email, :senha)";
        $st = $pdo->prepare($sql);

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $ok = $st->execute([
            ":nome"     => $nome,
            ":cpf"      => $cpf_numeros,
            ":telefone" => $telefone_numeros,
            ":email"    => $email,
            ":senha"    => $senha_hash
        ]);

        if ($ok) {
            redirecWith("../index.html", ["cadastro" => "ok"]);
        } else {
            redirecWith("../paginas/cadastro.html", ["erro" => "Erro ao cadastrar no banco de dados"]);
        }

    } catch (PDOException $e) {
        // Em produção você não deve expor a mensagem completa do erro ao usuário.
        redirecWith("../paginas/cadastro.html", ["erro" => "Erro no banco de dados: " . $e->getMessage()]);
    }
} else {
    // Se chamaram o script sem a ação correta, redireciona para o formulário
    redirecWith("../paginas/cadastro.html", ["erro" => "Método inválido ou ação não informada"]);
}
