<?php
// ==============================================
// CONEXÃO COM O BANCO DE DADOS
// ==============================================
require_once __DIR__ . "/conexao.php";

// ==============================================
// FUNÇÃO DE REDIRECIONAMENTO
// ==============================================
function redirect_with($url, $params = []) {
    if (!empty($params)) {
        $qs = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

// ==============================================
// LISTAGEM DE CUPONS (JSON PARA JS)
// ==============================================
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    header("Content-Type: application/json; charset=utf-8");
    try {
        $stmt = $pdo->query("SELECT idCupom AS id, nome, valor, data_validade, quantidade 
                             FROM Cupom ORDER BY nome");
        $cupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "ok" => true,
            "cupons" => $cupons
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $e) {
        echo json_encode([
            "ok" => false,
            "error" => "Erro ao listar cupons",
            "detail" => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ==============================================
// CADASTRO DE CUPOM
// ==============================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["acao"] ?? "") === "cadastrar") {
    try {
        $nome = trim($_POST["nome"] ?? "");
        $valor = floatval($_POST["valor"] ?? 0);
        $data_validade = trim($_POST["data_validade"] ?? "");
        $quantidade = intval($_POST["quantidade"] ?? 0);

        // Validação básica
        $erros = [];
        if ($nome === "") $erros[] = "Informe o nome do cupom.";
        if ($valor <= 0) $erros[] = "Informe um valor válido.";
        if ($data_validade === "") $erros[] = "Informe a data de validade.";
        if ($quantidade <= 0) $erros[] = "Informe a quantidade.";

        if (!empty($erros)) {
            redirect_with("../paginaslogista/promocoes_logista.html", ["erro_cupom" => implode(" ", $erros)]);
        }

        $sql = "INSERT INTO Cupom (nome, valor, data_validade, quantidade) 
                VALUES (:nome, :valor, :data_validade, :quantidade)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nome" => $nome,
            ":valor" => $valor,
            ":data_validade" => $data_validade,
            ":quantidade" => $quantidade
        ]);

        redirect_with("../paginaslogista/promocoes_logista.html", ["cadastro_cupom" => "ok"]);

    } catch (Throwable $e) {
        redirect_with("../paginaslogista/promocoes_logista.html", ["erro_cupom" => "Erro ao cadastrar: ".$e->getMessage()]);
    }
}

// ==============================================
// EDIÇÃO DE CUPOM
// ==============================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["acao"] ?? "") === "atualizar") {
    try {
        $id = intval($_POST["id"] ?? 0);
        $nome = trim($_POST["nome"] ?? "");
        $valor = floatval($_POST["valor"] ?? 0);
        $data_validade = trim($_POST["data_validade"] ?? "");
        $quantidade = intval($_POST["quantidade"] ?? 0);

        if ($id <= 0) {
            redirect_with("../paginaslogista/promocoes_logista.html", ["erro_cupom" => "ID inválido para edição."]);
        }

        $erros = [];
        if ($nome === "") $erros[] = "Informe o nome do cupom.";
        if ($valor <= 0) $erros[] = "Informe um valor válido.";
        if ($data_validade === "") $erros[] = "Informe a data de validade.";
        if ($quantidade <= 0) $erros[] = "Informe a quantidade.";

        if (!empty($erros)) {
            redirect_with("../paginaslogista/promocoes_logista.html", ["erro_cupom" => implode(" ", $erros)]);
        }

        $sql = "UPDATE Cupom 
                SET nome = :nome, valor = :valor, data_validade = :data_validade, quantidade = :quantidade
                WHERE idCupom = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nome" => $nome,
            ":valor" => $valor,
            ":data_validade" => $data_validade,
            ":quantidade" => $quantidade,
            ":id" => $id
        ]);

        redirect_with("../paginaslogista/promocoes_logista.html", ["editar_cupom" => "ok"]);

    } catch (Throwable $e) {
        redirect_with("../paginaslogista/promocoes_logista.html", ["erro_cupom" => "Erro ao editar: ".$e->getMessage()]);
    }
}

// ==============================================
// EXCLUSÃO DE CUPOM
// ==============================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["acao"] ?? "") === "excluir") {
    try {
        $id = intval($_POST["id"] ?? 0);
        if ($id <= 0) {
            redirect_with("../paginaslogista/promocoes_logista.html", ["erro_cupom" => "ID inválido para exclusão."]);
        }

        $stmt = $pdo->prepare("DELETE FROM Cupom WHERE idCupom = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        redirect_with("../paginaslogista/promocoes_logista.html", ["excluir_cupom" => "ok"]);

    } catch (Throwable $e) {
        redirect_with("../paginaslogista/promocoes_logista.html", ["erro_cupom" => "Erro ao excluir: ".$e->getMessage()]);
    }
}

?>
