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

/* Lê arquivo de upload como blob (ou null) */
function readImageToBlob(?array $file): ?string {
  if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
  $content = file_get_contents($file['tmp_name']);
  return $content === false ? null : $content;
}

try {
  // SE O METODO DE ENVIO FOR DIFERENTE DO POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecWith("../paginaslogista/cadastro_produtos_logista.html",
      ["erro_marca" => "Método inválido"]);
  }

  // criar as váriaveis do produtos
  $nome =$_POST["nomeprodutos"] ?? "";
  $descricao  = isset ($_POST['descricao'] ?? null) $_POST['descricao'];
  $quantidade =(int)$_POST["quantidade"];
$preco =(double)$_POST["preco"];
$tamanho =$_POST["tamanho"];
$cor =$_POST["cor"];
$codigo =(int)$_POST["codigo"];
$preco_promocional =(double)($_POST)["preco_promocional"];


// Várriavies das imagens
$img1 = readImageToBlob($_FILES["img1produto"]?? null);
$img1 = readImageToBlob($_FILES["img2produto"]?? null);
$img1 = readImageToBlob($_FILES["img3produto"]?? null);



  // VALIDANDO OS CAMPOS
  $erros_validacao = [];
  if ($nome === "" || $descricao ==="" || $quantidade === "" $preco ==) {
    $erros_validacao[] = "Preencha os campos obrigatório.";
  }

  // se houver erros, volta para a tela com a mensagem
  if (!empty($erros_validacao)) {
    redirecWith("../paginaslogista/cadastro_produtos_logista.html",
      ["erro_marca" => implode(" ", $erros_validacao)]);
  }


// é utilizado para fazer vinculos de transações
$pdo -> beginTransaction( );

// fazer o comando de insert dentro da tabela de produtos
$sqlProdutos ="INSERT INTO produtos
(nome, descricao, quantidade, preco, tamanho, cor, codigo, preco_promocional)
VALUES 
(:nome, :descricao, :quantidade, :preco, :tamanho, :cor, :codigo, :preco_promocional)";

$sqlImagens = "INSERT INTO imagem_produtos(foto) VALUES (imagem),
(imagem2),
(imagem3)";




$stmProdutos= $pdo -> prepare ($sqlProdutos);
$inserirProdutos= $stmProdutos->execute([
":nome"=>$nome,
":descricao"=>$descricao,
":quantidade"=>(int)$quantidade,
":preco"=>$preco,
":tamanho"=>$tamanho,
"codigo"=>$codigo,
":preco_promocional"=>$preco_promocional,
]);




if(InsertProdutos){
$pdo->rolBack();
session_start();

$_SESSION



}














  // INSERT
  $sql  = "INSERT INTO Marcas (nome, imagem) VALUES (:nome, :img)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":nome", $nomemarca, PDO::PARAM_STR);

  if ($imgBlob === null) {
    $stmt->bindValue(":img", null, PDO::PARAM_NULL);
  } else {
    $stmt->bindValue(":img", $imgBlob, PDO::PARAM_LOB);
  }

  $ok = $stmt->execute();

  if ($ok) {
    redirecWith("../paginaslogista/cadastro_produtos_logista.html",
      ["cadastro_marca" => "ok"]);
  } else {
    redirecWith("../paginaslogista/cadastro_produtos_logista.html",
      ["erro_marca" => "Falha ao cadastrar marca."]);
  }

} catch (Exception $e) {
  redirecWith("../paginaslogista/cadastro_produtos_logista.html",
    ["erro_marca" => "Erro no banco de dados: " . $e->getMessage()]);
}
