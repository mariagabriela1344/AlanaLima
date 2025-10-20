<?php
// Conectando ao banco de dados
require_once __DIR__ ."/conexao.php";

// Função para redirecionamento
function redirecWith($url, $params = []) {
    if(!empty($params)){
        $qs = http_build_query($params);
        $sep = (strpos($url,'?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

// ==============================================
// LISTAGEM DE CUPONS
// ==============================================
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    try {
        $sqlListar = "SELECT idCupom AS id, nome, valor, data_validade, quantidade 
                      FROM Cupom ORDER BY nome";

        $stmtListar = $pdo->query($sqlListar);
        $listar = $stmtListar->fetchAll(PDO::FETCH_ASSOC);

        $formato = isset($_GET["format"]) ? strtolower($_GET["format"]) : "option";

        if ($formato === "json") {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["ok" => true, "cupons" => $listar], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        foreach ($listar as $cupom) {
            $id   = (int)$cupom["id"];
            $nome = htmlspecialchars($cupom["nome"], ENT_QUOTES, "UTF-8");
            echo "<option value=\"{$id}\">{$nome} - R$ {$cupom['valor']}</option>\n";
        }
        exit;

    } catch (Throwable $e) {
        if ($formato === "json") {
            header('Content-Type: application/json; charset=utf-8', true, 500);
            echo json_encode(['ok' => false, 'error' => 'Erro ao listar cupons', 
                             'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/html; charset=utf-8', true, 500);
            echo "<option disabled>Erro ao carregar cupons</option>";
        }
        exit;
    }
}

// ==============================================
// CADASTRO DE CUPOM
// ==============================================
try {
    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        redirecWith("../paginaslogista/promocoes_logista.html", ["erro" => "Método inválido"]);
    }

    // Recebendo dados do formulário
    $nome = $_POST["nome"] ?? "";
    $valor = (double)$_POST["valor"] ;
    $data_validade = $_POST["validade"] ?? "";
    $quantidade = (int)$_POST["quantidade"] ;

    // Validação básica
    $erros_validacao = [];
    if($nome === "" || $valor <=0 || $data_validade === "" || $quantidade <=0){
        $erros_validacao[] = "Preencha todos os campos corretamente.";
    }

    if(!empty($erros_validacao)){
        redirecWith("../paginaslogista/promocoes_logista.html", ["erro" => implode(", ", $erros_validacao)]);
    }

    // Inserção no banco de dados
    $sql = "INSERT INTO Cupom (nome, valor, data_validade, quantidade) 
            VALUES (:nome, :valor, :data_validade, :quantidade)";
    
    $inserir = $pdo->prepare($sql)->execute([
        ":nome" => $nome,
        ":valor" => $valor,
        ":data_validade" => $data_validade,
        ":quantidade" => $quantidade
    ]);

    if($inserir){
        redirecWith("../paginaslogista/promocoes_logista.html", ["cadastro" => "ok"]);
    } else {
        redirecWith("../paginaslogista/promocoes_logista.html", ["erro" => "Erro ao cadastrar no banco de dados"]);
    }

} catch(Exception $e){
    redirecWith("../paginaslogista/promocoes_logista.html", ["erro" => "Erro no banco de dados: ".$e->getMessage()]);
}
?>
