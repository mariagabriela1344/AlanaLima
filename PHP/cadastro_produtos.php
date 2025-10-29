<?php
// ============================
// CONEXÃO
// ============================
require_once __DIR__ . "/conexao.php";


// ============================
// 1) LISTAR PRODUTOS (RETORNA JSON)
// ============================
if (isset($_GET['listar'])) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $sql = "SELECT p.idProdutos AS id,
                       p.nome,
                       p.descricao,
                       p.quantidade,
                       p.preco,
                       p.preco_promocional,
                       p.tamanho,
                       p.cor,
                       p.codigo,
                       i.foto AS imagem
                FROM Produtos p
                LEFT JOIN Produtos_has_Imagem_produtos pi
                       ON p.idProdutos = pi.Produtos_idProdutos
                LEFT JOIN Imagem_produtos i
                       ON pi.Imagem_produtos_idImagem_produtos = i.idImagem_produtos
                ORDER BY p.idProdutos DESC";

        $stm = $pdo->prepare($sql);
        $stm->execute();

        $produtosTemp = [];
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            $id = $row['id'];
            if (!isset($produtosTemp[$id])) {
                $produtosTemp[$id] = [
                    'id' => $id,
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'quantidade' => (int)$row['quantidade'],
                    'preco' => (float)$row['preco'],
                    'preco_promocional' => (float)$row['preco_promocional'],
                    'tamanho' => $row['tamanho'],
                    'cor' => $row['cor'],
                    'codigo' => $row['codigo'],
                    'imagens' => []
                ];
            }

            if (!empty($row['imagem'])) {
                $produtosTemp[$id]['imagens'][] = base64_encode($row['imagem']);
            }
        }

        echo json_encode([
            'ok' => true,
            'produtos' => array_values($produtosTemp)
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}


// ============================
// 2) FUNÇÕES AUXILIARES
// ============================
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    return file_get_contents($file['tmp_name']);
}
/*  ============================ ATUALIZAÇÃO - FORMAS DE PAGAMENTO =========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
  try {
    // ---- Campos vindos do formulário HTML ----
    $id        = (int)($_POST['id'] ?? 0);
    $nome      = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $tipo      = trim($_POST['tipo'] ?? '');
    $taxa      = trim($_POST['taxa'] ?? '');
    $ativo     = isset($_POST['ativo']) && ($_POST['ativo'] === '1' || $_POST['ativo'] === 'on') ? 1 : 0;

    // ---- Validações ----
    if ($id <= 0) {
      redirect_with('../PAGINAS_LOGISTA/formas_pagamento.html', ['erro_pagamento' => 'ID inválido para edição.']);
    }

    $erros = [];

    if ($nome === '') {
      $erros[] = 'Informe o nome da forma de pagamento.';
    } elseif (mb_strlen($nome) > 45) {
      $erros[] = 'Nome deve ter no máximo 45 caracteres.';
    }

    if ($descricao !== '' && mb_strlen($descricao) > 150) {
      $erros[] = 'Descrição deve ter no máximo 150 caracteres.';
    }

    if ($tipo !== '' && mb_strlen($tipo) > 45) {
      $erros[] = 'Tipo deve ter no máximo 45 caracteres.';
    }

    // Validação de taxa (opcional)
    $taxaFloat = null;
    if ($taxa !== '') {
      $taxa = str_replace(',', '.', $taxa);
      if (!is_numeric($taxa)) {
        $erros[] = 'Taxa inválida. Utilize apenas números (ex: 2.5).';
      } else {
        $taxaFloat = (float)$taxa;
      }
    }

    if ($erros) {
      redirect_with('../PAGINAS_LOGISTA/formas_pagamento.html', ['erro_pagamento' => implode(' ', $erros)]);
    }

    // ---- Atualização no banco ----
    // Ajuste os nomes dos campos conforme sua tabela (exemplo: FormaPagamento)
    $sql = "UPDATE FormaPagamento
              SET nome = :nome,
                  descricao = :descricao,
                  tipo = :tipo,
                  taxa = :taxa,
                  ativo = :ativo
            WHERE idFormaPagamento = :id";

    $st = $pdo->prepare($sql);

    $st->bindValue(':nome', $nome, PDO::PARAM_STR);
    $st->bindValue(':descricao', $descricao === '' ? null : $descricao, $descricao === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $st->bindValue(':tipo', $tipo === '' ? null : $tipo, $tipo === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    if ($taxaFloat === null) {
      $st->bindValue(':taxa', null, PDO::PARAM_NULL);
    } else {
      $st->bindValue(':taxa', $taxaFloat);
    }
    $st->bindValue(':ativo', $ativo, PDO::PARAM_INT);
    $st->bindValue(':id', $id, PDO::PARAM_INT);

    $st->execute();

    // ---- Redirecionamento de sucesso ----
    redirect_with('../PAGINAS_LOGISTA/formas_pagamento.html', ['editar_pagamento' => 'ok']);

  } catch (Throwable $e) {
    // ---- Tratamento de erro ----
    redirect_with('../PAGINAS_LOGISTA/formas_pagamento.html', ['erro_pagamento' => 'Erro ao editar: ' . $e->getMessage()]);
  }
}


/*  ============================EXCLUSÃO=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
  try {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {

// alterar para o nome da página html que você está utilizando
      redirect_with('../PAGINAS_LOGISTA/banners_logista.html', ['erro_banner' => 'ID inválido para exclusão.']);
    }

// alterar os dados para os nomes que vem do seu banco de dados
    $st = $pdo->prepare("DELETE FROM Banners WHERE idBanners = :id");
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();
// alterar para o nome da página html que você está utilizando

    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['excluir_banner' => 'ok']);

  } catch (Throwable $e) {
// alterar para o nome da página html que você está utilizando

    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_banner' => 'Erro ao excluir: ' . $e->getMessage()]);
  }
}


// ============================
// 3) CADASTRAR PRODUTO (POST)
// ============================
try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginaslogista/cadastro_produtos_logista.html", [
            "erro" => "Método inválido"
        ]);
    }

    // Criação das variáveis
    $nome = $_POST["nomeproduto"] ?? "";
    $descricao = $_POST["descricao"] ?? "";
    $quantidade = (int)($_POST["quantidade"] ?? 0);
    $preco = (double)($_POST["preco"] ?? 0);
    $tamanho = $_POST["tamanho"] ?? "";
    $cor = $_POST["cor"] ?? "";
    $codigo = (int)($_POST["codigo"] ?? 0);
    $preco_promocional = (double)($_POST["precopromocional"] ?? 0);

    // Imagens
    $img1 = readImageToBlob($_FILES["imgproduto1"] ?? null);
    $img2 = readImageToBlob($_FILES["imgproduto2"] ?? null);
    $img3 = readImageToBlob($_FILES["imgproduto3"] ?? null);

    // Validação
    $erros_validacao = [];
    if ($nome === "" || $descricao === "") {
        $erros_validacao[] = "Preencha os campos obrigatórios.";
    }

    if (!empty($erros_validacao)) {
        redirecWith("../paginaslogista/cadastro_produtos_logista.html", [
            "erro_marca" => implode(" ", $erros_validacao)
        ]);
    }

    // Iniciar transação
    $pdo->beginTransaction();

    // Inserir produto
    $sqlProdutos = "INSERT INTO Produtos 
        (nome, descricao, quantidade, preco, tamanho, cor, codigo, preco_promocional)
        VALUES 
        (:nome, :descricao, :quantidade, :preco, :tamanho, :cor, :codigo, :preco_promocional)";
    $stmProdutos = $pdo->prepare($sqlProdutos);
    $stmProdutos->execute([
        ":nome" => $nome,
        ":descricao" => $descricao,
        ":quantidade" => $quantidade,
        ":preco" => $preco,
        ":tamanho" => $tamanho,
        ":cor" => $cor,
        ":codigo" => $codigo,
        ":preco_promocional" => $preco_promocional
    ]);

    $idProduto = (int)$pdo->lastInsertId();

    // Inserir imagens
    $sqlImagem = "INSERT INTO Imagem_produtos (foto) VALUES (:foto)";
    $stmImagem = $pdo->prepare($sqlImagem);
    $idsImagens = [];

    foreach ([$img1, $img2, $img3] as $img) {
        if ($img !== null) {
            $stmImagem->bindParam(":foto", $img, PDO::PARAM_LOB);
            $stmImagem->execute();
            $idsImagens[] = (int)$pdo->lastInsertId();
            $stmImagem->closeCursor();
        }
    }

    if (empty($idsImagens)) {
        throw new Exception("Nenhuma imagem válida foi enviada.");
    }

    // Vincular imagens
    $sqlVinculo = "INSERT INTO Produtos_has_Imagem_produtos 
        (Produtos_idProdutos, Imagem_produtos_idImagem_produtos)
        VALUES (:idProduto, :idImagem)";
    $stmVinculo = $pdo->prepare($sqlVinculo);
    foreach ($idsImagens as $idImg) {
        $stmVinculo->execute([
            ":idProduto" => $idProduto,
            ":idImagem" => $idImg
        ]);
    }

    $pdo->commit();

    redirecWith("../paginaslogista/cadastro_produtos_logista.html", [
        "cadastro_produto" => "ok"
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    redirecWith("../paginaslogista/cadastro_produtos_logista.html", [
        "erro_marca" => "Erro ao cadastrar produto: " . $e->getMessage()
    ]);
}
