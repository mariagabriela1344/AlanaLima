<?php
// =======================================================
// CONEXÃO COM O BANCO DE DADOS
// =======================================================
require_once __DIR__ . "/conexao.php";

// =======================================================
// FUNÇÃO DE REDIRECIONAMENTO
// =======================================================
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

// =======================================================
// LISTAR FORMAS DE PAGAMENTO (GET)
// =======================================================
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    try {
        $sql = "SELECT idformas_pagamento AS id, nome 
                FROM Formas_pagamento 
                ORDER BY nome";
        $stmt = $pdo->query($sql);
        $formas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formato = isset($_GET["format"]) ? strtolower($_GET["format"]) : "option";

        if ($formato === "json") {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["ok" => true, "formas_pagamento" => $formas], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header("Content-Type: text/html; charset=utf-8");
        foreach ($formas as $f) {
            $id = (int)$f["id"];
            $nome = htmlspecialchars($f["nome"], ENT_QUOTES, "UTF-8");
            echo "<option value=\"{$id}\">{$nome}</option>\n";
        }
        exit;

    } catch (Throwable $e) {
        if (isset($_GET["format"]) && strtolower($_GET["format"]) === "json") {
            header("Content-Type: application/json; charset=utf-8", true, 500);
            echo json_encode(["ok" => false, "error" => "Erro ao listar formas de pagamento", "detail" => $e->getMessage()]);
        } else {
            header("Content-Type: text/html; charset=utf-8", true, 500);
            echo "<option disabled>Erro ao carregar formas de pagamento</option>";
        }
        exit;
    }
}

// =======================================================
// CADASTRAR FORMA DE PAGAMENTO (POST)
// =======================================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["acao"])) {
    try {
        $nomepagamento = trim($_POST["nomepagamento"] ?? "");

        if ($nomepagamento === "") {
            redirecWith("../PAGINASLOGISTA/pagamentos_fretes.html", ["erro" => "Preencha o nome da forma de pagamento"]);
        }

        $sql = "INSERT INTO Formas_pagamento (nome) VALUES (:nome)";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([":nome" => $nomepagamento]);

        if ($ok) {
            redirecWith("../PAGINASLOGISTA/pagamentos_fretes.html", ["cadastro" => "ok"]);
        } else {
            redirecWith("../PAGINASLOGISTA/pagamentos_fretes.html", ["erro" => "Erro ao cadastrar no banco"]);
        }

    } catch (Throwable $e) {
        redirecWith("../PAGINASLOGISTA/pagamentos_fretes.html", ["erro" => "Erro: " . $e->getMessage()]);
    }
}

// =======================================================
// ATUALIZAR FORMA DE PAGAMENTO
// =======================================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["acao"] ?? "") === "atualizar") {
    try {
        $id = (int)($_POST["id"] ?? 0);
        $nomepagamento = trim($_POST["nomepagamento"] ?? "");

        if ($id <= 0 || $nomepagamento === "") {
            redirecWith("../PAGINASLOGISTA/pagamentos_fretes.html", ["erro_pagamento" => "Preencha todos os campos corretamente."]);
        }

        $sql = "UPDATE Formas_pagamento SET nome = :nome WHERE idformas_pagamento = :id";
        $st = $pdo->prepare($sql);
        $st->bindValue(":nome", $nomepagamento, PDO::PARAM_STR);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        redirecWith("../PAGINASLOGISTA/pagamentos_fretes.html", ["editar_pagamento" => "ok"]);
    } catch (Throwable $e) {
        redirecWith("../PAGINASLOGISTA/pagamentos_fretes.html", ["erro_pagamento" => "Erro ao editar: " . $e->getMessage()]);
    }
}

// =======================================================
// EXCLUIR FORMA DE PAGAMENTO
// =======================================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["acao"] ?? "") === "excluir") {
    try {
        $id = (int)($_POST["id"] ?? 0);
        if ($id <= 0) {
            redirecWith("../PAGINASLOGISTA/pagamentos_fretes.html", ["erro_pagamento" => "ID inválido para exclusão."]);
        }

        $st = $pdo->prepare("DELETE FROM Formas_pagamento WHERE idformas_pagamento = :id");
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        redirecWith("../PAGINASLOGISTA/pagamentos_fretes.html", ["excluir_pagamento" => "ok"]);

    } catch (Throwable $e) {
        redirecWith("../PAGINASLOGISTA/pagamentos_fretes.html", ["erro_pagamento" => "Erro ao excluir: " . $e->getMessage()]);
    }
}
?>
