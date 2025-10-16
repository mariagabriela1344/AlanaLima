<?php
// Conectando este arquivo ao banco de dados
require_once __DIR__ . "/conexao.php";

// função para capturar os dados passados de uma página a outra
function redirecWith($url, $params = []) {
  // verifica se os os paramentros não vieram vazios
  if (!empty($params)) {
    // separar os parametros em espaços diferentes
    $qs  = http_build_query($params);
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    $url .= $sep . $qs;
  }
  // joga a url para o cabeçalho no navegador
  header("Location: $url");
  // fecha o script
  exit;
}

function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    return file_get_contents($file['tmp_name']);
}

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
  $inserirProdutos = $stmProdutos->execute([
    ":nome" => $nome,
    ":descricao" => $descricao,
    ":quantidade" => $quantidade,
    ":preco" => $preco,
    ":tamanho" => $tamanho,
    ":cor" => $cor,
    ":codigo" => $codigo,
    ":preco_promocional" => $preco_promocional
  ]);

  if (!$inserirProdutos) {
    throw new Exception("Falha ao inserir produto");
  }

  $idProduto = (int)$pdo->lastInsertId();

  // INSERIR IMAGENS E VINCULAR AO PRODUTO
$sqlImagem = "INSERT INTO Imagem_produtos (foto) VALUES (:foto)";
$stmImagem = $pdo->prepare($sqlImagem);

$idsImagens = [];

foreach ([$img1, $img2, $img3] as $img) {
    if ($img !== null) {
        $stmImagem->bindParam(":foto", $img, PDO::PARAM_LOB);
        $stmImagem->execute();
        $idsImagens[] = (int)$pdo->lastInsertId();

        // Limpar parâmetro entre execuções (necessário no PDO)
        $stmImagem->closeCursor();
    }
}

// SE NÃO INSERIU NENHUMA IMAGEM, LANÇAR ERRO
if (empty($idsImagens)) {
    throw new Exception("Nenhuma imagem válida foi enviada.");
}

// VINCULAR IMAGENS AO PRODUTO
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
  // Vincular imagens ao produto
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

  // Confirmar transação
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