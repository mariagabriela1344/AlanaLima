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
// LISTAR FRETES (GET)
// =======================================================
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    try {
        $sql = "SELECT idFretes AS id, bairro, valor, transportadora
                FROM Fretes
                ORDER BY bairro, valor";
        $stmt = $pdo->query($sql);
        $fretes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formato = isset($_GET["format"]) ? strtolower($_GET["format"]) : "option";

        if ($formato === "json") {
            $saida = array_map(fn($f) => [
                "id" => (int)$f["id"],
                "bairro" => $f["bairro"],
                "valor" => (float)$f["valor"],
                "transportadora" => $f["transportadora"],
            ], $fretes);

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["ok" => true, "fretes" => $saida], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Retorno HTML <option>
        header("Content-Type: text/html; charset=utf-8");
        foreach ($fretes as $f) {
            $id = (int)$f["id"];
            $bairro = htmlspecialchars($f["bairro"], ENT_QUOTES, "UTF-8");
            $transp = $f["transportadora"] ? " (" . htmlspecialchars($f["transportadora"], ENT_QUOTES, "UTF-8") . ")" : "";
            $valorFmt = number_format((float)$f["valor"], 2, ",", ".");
            echo "<option value=\"{$id}\">{$bairro}{$transp} - R$ {$valorFmt}</option>\n";
        }
        exit;

    } catch (Throwable $e) {
        if (($formato ?? '') === "json") {
            header("Content-Type: application/json; charset=utf-8", true, 500);
            echo json_encode([
                "ok" => false,
                "error" => "Erro ao listar fretes",
                "detail" => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        } else {
            header("Content-Type: text/html; charset=utf-8", true, 500);
            echo "<option disabled>Erro ao carregar fretes</option>";
        }
        exit;
    }
}

// =======================================================
// CADASTRAR FRETE (POST)
// =======================================================
try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginaslogista/pagamentos_fretes.html", ["erro" => "Método inválido"]);
    }

    // Recebe dados do formulário
    $bairro = trim($_POST["bairro"] ?? "");
    $valor = (float)($_POST["valor"] ?? 0);
    $transportadora = trim($_POST["transportadora"] ?? "");

    // Validação básica
    if ($bairro === "" || $valor <= 0) {
        redirecWith("../paginaslogista/pagamentos_fretes.html", ["erro" => "Preencha todos os campos corretamente"]);
    }

    // Inserção no banco
    $sql = "INSERT INTO Fretes (bairro, valor, transportadora)
            VALUES (:bairro, :valor, :transportadora)";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        ":bairro" => $bairro,
        ":valor" => $valor,
        ":transportadora" => $transportadora
    ]);

    if ($ok) {
        redirecWith("../paginaslogista/pagamentos_fretes.html", ["cadastro" => "ok"]);
    } else{
        redirecWith("../paginaslogista/pagamentos_fretes.html", ["erro" => "Erro ao cadastrar no banco de dados"]);
    }

} catch (Exception $e) {
    redirecWith("../paginaslogista/pagamentos_fretes.html", [
        "erro" => "Erro no banco de dados: " . $e->getMessage()
    ]);
}
?>