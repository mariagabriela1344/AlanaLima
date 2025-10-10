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
function readImageToBlob(?array $file): ?string {
    if (
        !$file ||
        !isset($file['tmp_name']) ||
        $file['error'] !== UPLOAD_ERR_OK
    ) {
        return null;
    }

    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

try{
// SE O METODO DE ENVIO FOR DIFERENTE DO POST
    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        //VOLTAR À TELA DE CADASTRO E EXIBIR ERRO
        redirecWith("../paginas_logista/cadastro_produtos_logista.html",
           ["erro"=> "Metodo inválido"]);
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

// VÁRIAVEIS  das imagens
$img1  = readImageToBlod($_FILES["imgproduto1"] ?? null);
$img2  = readImageToBlod($_FILES["imgproduto2"] ?? null);
$img3  = readImageToBlod($_FILES["imgproduto3"] ?? null);


//VALIDANDO OS CAMPOS
$erros_validacao =[];
if ($nome = "") || $descricao === "" || $quantidade === 0  ""
|| $preco =  0 "" ||{
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

$sqlImagens = "INSERT INTO imagem_produtos(foto) VALUES (imagem1),
(imagem2),
(imagem3)";


$stmProdutos= $pdo -> prepare ($sqlProdutos);

$inserirProdutos=$stmProdutos->execute([
":nome"=>$nome,
":descricao"=>$descricao,
":quantidade"=>(int)$quantidade,
":preco"=>$preco,
":tamanho"=>$tamanho,
":cor"=>$cor,
":codigo"=>$codigo,
":preco_promocional"=>$preco_promocional,
]);


if(!InserirProdutos){
$pdo ->rollBack();
redirecWith("../paginas_logista/cadastro_produtos_logista.html"
["Error"=> "Falhar ao cadastrar produtos"]);

}
 $idproduto=(int)$pdo->lastinsertid();



// INSERIR IMAGENS

$sqlImagens = "INSERT INTO imagem_produtos(foto) VALUES (imagem1)",
(imagem2),
(imagem3);

$stmlImagens=$pdo -> prepare ($sqlImagens);

$inserirImagens =$stmimganes->execute([
    "imagem1"=> $img1,
        "imagem2"=> $img2,
            "imagem3"=> $img3,
]);

//PREPARA O COMANDO SQL PARA SER EXECUTADO
$stmImagens = $pdo -> prepare ($sqlImagens);

/*Bind como LOB quando houver conteúdo; se null,
o PDO envia NULL corretamente*/

if ($img1 !== null){
$stmImagens->bindParam(':imagem', $img1, PDO::PARAM_LOB);
}else{
$stmImagens->bindValue(':imagem1', null, PDO::PARAM_NULL);
}

if ($img2 !== null){
$stmImagens->bindParam(':imagem', $img2, PDO::PARAM_LOB);
}else{
$stmImagens->bindValue(':imagem1', null, PDO::PARAM_NULL);
}

if ($img3 !== null){
$stmImagens->bindParam(':imagem', $img3, PDO::PARAM_LOB);
}else{
$stmImagens->bindValue(':imagem1', null, PDO::PARAM_NULL);
}

$inserirImagens = $stmImagens->execute();


// VERIFICAR SE O INSERIR IMAGENS DEU ERRADO
if(!InserirProdutos){
$pdo ->rollBack();
redirecWith("../paginas_logista/cadastro_produtos_logista.html"
["Error"=> "Falhar ao cadastrar produtos"]);
}
// CASO TENHA DADO CERTO, CAPTURE O ID DA IMAGEM CADASTRADA
$idImg = (int) $pdo ->lastInsertId();


// VINCULAR A IMAGEM COM O PRODUTO
$sqlVincularProdImg = "INSERT INTO Produtos_has_imagem_produtos
(Produtos_idProduto,Imagem_produtos_idImagem_produtos) VALUES
(:idpro, :idimg)";

$stmVincularProdImg = $pdo ->prepare($sqlVincularProdImg);

$inserirVincularProdImg = $stmVincularProdImg-> execute ([
":idpro" => $idproduto,
":idimg" => $idImg,
]);

if(!InserirVincularProdImg){
$pdo ->rollBack();
redirecWith("../paginas_logista/cadastro_produtos_logista.html"
["Error"=> "Falhar ao vincular produtos com iamgem"]);



}

}catch(Exception $e){
 redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro" => "Erro no banco de dados: "
      .$e->getMessage()]);
}