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

/*  ============================ATUALIZAÇÃO=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') 
  try {
    $id        = (int)($_POST['id'] ?? 0);
    $bairro = trim($_POST['bairro'] ?? '');
    $Valor = trim($_POST['Valor'] ?? '');
    $transportadora   = trim($_POST['transportadora'] ?? '');
    $categoria = ($categoria === '' || $categoria === null) ? null : (int)$categoria;

    if ($id <= 0) {
      redirect_with('../PAGINAS_LOGISTA/pagamentos_fretes.html', ['erro_frete' => 'ID inválido para edição.']);
    }

    
    // validações mínimas (iguais ao cadastro)
    $erros = [];
    if ($bairro === '') { $erros[] = 'Informe o bairro.'; }
    elseif (mb_strlen($bairro) > 1) { $erros[] = 'o bairro deve ter pelo menos uma informação.'; }

    $valor= DateTime::createFromFormat('Y-m-d', $valor);
    if (!($dt && $dt->format('Y-m-d') === $valor)) { $erros[] = 'valor inválido (use YYYY-MM-DD).'; }

     if ($transportadora === '') { $erros[] = 'Informe a transportadora.'; }
    elseif (mb_strlen($transportadora) > 0) { 



    if ($erros) {
      redirect_with('../PAGINAS_LOGISTA/pagamentos_fretes.html', ['erro_frete' => implode(' ', $erros)]);
    }

    // Monta UPDATE dinâmico (atualiza imagem só se uma nova foi enviada)
    $setSql = "descricao = :desc, data_validade = :dt, link = :lnk, CategoriasProdutos_id = :cat";
    if ($imgBlob !== null) {
      $setSql = "imagem = :img, " . $setSql;
    }

    $sql = "UPDATE Fretes
              SET $setSql
            WHERE idfretes = :idFretes";

    $st = $pdo->prepare($sql);

    

    $st->bindValue(':desc', $bairro, PDO::PARAM_STR);
    $st->bindValue(':dt',   $valor,   PDO::PARAM_STR);

    

    if ($categoria === null) {
      $st->bindValue(':cat', null, PDO::PARAM_NULL);
    } else {
      $st->bindValue(':cat', $categoria, PDO::PARAM_INT);
    }

    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();

    redirect_with('../PAGINAS_LOGISTA/pagamentos_fretes.html', ['editar_banner' => 'ok']);

  } catch (Throwable $e) {
    redirect_with('../PAGINAS_LOGISTA/pagamentos_fretes.html', ['erro_banner' => 'Erro ao editar: ' . $e->getMessage()]);
  }
}




/*  ============================EXCLUSÃO=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
  try {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
      redirect_with('../PAGINAS_LOGISTA/pagamentos_fretes.html', ['erro_frete' => 'ID inválido para exclusão.']);
    }

    $st = $pdo->prepare("DELETE FROM FRETE WHERE idFretes = :id");
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();

    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['excluir_frete' => 'ok']);

  } catch (Throwable $e) {
    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_frete' => 'Erro ao excluir: ' . $e->getMessage()]);
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