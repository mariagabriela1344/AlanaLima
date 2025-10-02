<?php
// Conectando este arquivo ao banco de dados
require_once __DIR__ ."/conexao.php";

// função para capturar os dados passados de uma página a outra
function redirecWith($url,$params=[]){
// verifica se os os paramentros não vieram vazios
 if(!empty($params)){
// separar os parametros em espaços diferentes
$qs= http_build_query($params);
$sep = (strpos($url,'?') === false) ? '?': '&';
$url .= $sep . $qs;
}
// joga a url para o cabeçalho no navegador
header("Location:  $url");
// fecha o script
exit;
}

/* LÊ arquivo de upload como blod (ou null)*/
function readImageToBlod(?array $file): ?string{
    if(!$file || !isset($file["tmp_name"])) || $file['error'] !== UPLOAD_ERR_OK ) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false? null : $content;
}

// criar as váriaveis do produtos
$nome = $_POST["nomeproduto"];
$descricao = $_POST["descricao"];
$quantidade = (int)$_POST["quantidade"];
$preco = (double)$_POST["preco"];
$tamanho = $_POST["tamanho"];
$cor = $_POST["cor"];
$codigo = (int) $_POST["codigo"];
$preco_promocional = (double)$_POST["precopromocinal"];

//criar as váriaveis das imagens
$img1  = readImageToBlod($_FILES["imgproduto1"] ?? null);
$img2  = readImageToBlod($_FILES["imgproduto2"] ?? null);
$img3  = readImageToBlod($_FILES["imgproduto3"] ?? null);

//VALIDANDO OS CAMPOS
$erros_validacao =[];
if ($nome === "") || $descricao === "" || $quantidade === 0  ""
|| $preco ===  0 "" ||{
    $erros_validacao[]= "Preencha os campos obrigatórios.";
}

// se houver erros, volta para a tela com a mensagem
if (!empty($erros_validacao)){
    redirecWith ("../paginaslogista/cadastro_produtos_logista.html",)
    ["erros_marcas" => implode("", $erros_validacao)];
}

// é utilizado para fazer vinculos de transações
$pdo -> beginTransaction( );

// fazer o comando de insert dentro da tabela de produtos
$sqlProdutos = "INSERT INTO" Produtos(nome,descricao,quantidade,preco,tamanho,cor,codigo,preco_promocional,)
VALUES (:nome,:descricao,:quantidade,:preco,:tamanho,:cor,:codigo,:preco_promocional)'';

$stmProdutos= $pdo -> prepare ($sqlProdutos);

$inserirProdutos=$stmProdutos->execute([

]);