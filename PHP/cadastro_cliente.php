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

// capturando os dados e jogando em váriaveis
try{
    // SE O METODO DE ENVIO FOR DIFERENTE DO POST
    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        //VOLTAR À TELA DE CADASTRO E EXIBIR ERRO
        redirecWith("../paginas/cadastro.html",
           ["erro"=> "Metodo inválido"]);
    }
    // jogando os dados da dentro de váriaveis
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];    
    $telefone = $_POST["telefone"];
    $cpf = $_POST["cpf"];
    $confirmarsenha = $_POST["confirmar"];

    // VALIDANDO OS CAMPOS
// criar uma váriavel para receber os erros de validação
    $erros_validacao=[];
    //se qualquer campo for vazio
    if($nome === "" || $email ==="" || $senha === ""
    || $telefone === "" || $cpf==="" || 
    $confirmarsenha===""){
        $erros_validacao[]="Preencha todos os campos";
    }
    // validação para verificar se o email tem @
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $erros_validacao[]= "E-mail inválido";
    }
    // verificar se senha e confirmar senha são diferentes
    if($senha !== $confirmarsenha){
        $erros_validacao[]= "As senhas não conferem";
    }
    // verificar se a senha tem mais de 8 digitos
    if(strlen($senha)<8){
 $erros_validacao[]= 
 "Senha deve ter pelo menos 8 caracteres";
    }
    // verificar se o telefone tem pelo menos 11 digitos
     if(strlen($telefone)<11){
        $erros_validacao[]= "Telefone incorreto";
    }
    // verificar se o cpf tem pelo menos 11 digitos
     if(strlen($cpf)<11){
        $erros_validacao[]= "CPF inválido";
    }
    // agora é enviar os erros para a tela de cadastro
    if($erros_validacao){
        redirecWith("../paginas/cadastro.html" , 
        ["erro" => $erros_validacao[0]]);
    }

    // verificar se o cpf já foi cadastro no banco de dados
    $stmt = $pdo->prepare("SELECT * From Cliente 
    Where cpf= :cpf LIMIT 1");
    // joga o cpf digitado dentro do banco de dados
    $stmt ->execute([':cpf'=>$cpf]);
    // se o cpf já existir ele volta a tela cadastro
    if($stmt->fetch()){
        redirecWith('../paginas/cadastro.html',["erro" 
    => "CPF já está cadastrado"]);
    }
    /* Inserir o Cliente no banco de dados */
    $sql ="INSERT INTO 
    Cliente (nome,cpf,telefone,email,senha)
     Values (:nome,:cpf,:telefone,:email,:senha)";
     // executando o comando no banco de dados
     $inserir = $pdo->prepare($sql)->execute([
        ":nome" => $nome,
        ":cpf"=> $cpf,
        ":telefone"=> $telefone,
        ":email" => $email,
        ":senha"=> $senha,

/*  ============================ATUALIZAÇÃO=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
  try {
    $id        = (int)($_POST['id'] ?? 0);
    $CPF = trim($_POST['CPF'] ?? '');
    $Email  = trim($_POST['E-mail'] ?? '');
    $link      = trim($_POST['esqueci a senha'] ?? '');
    $categoria = ($categoria === '' || $categoria === null) ? null : (int)$categoria;

    if ($id <= 0) {
      redirect_with('../PAGINAS_LOGISTA/banners_logista.html', ['erro_banner' => 'ID inválido para edição.']);
    }

    // Lê (se houver) nova imagem
    $imgBlob = read_image_to_blob($_FILES['foto'] ?? null);

    // validações mínimas (iguais ao cadastro)
    $erros = [];
    if ($descricao === '') { $erros[] = 'Informe a descrição.'; }
    elseif (mb_strlen($descricao) > 45) { $erros[] = 'Descrição deve ter no máximo 45 caracteres.'; }

    $dt = DateTime::createFromFormat('Y-m-d', $dataVal);
    if (!($dt && $dt->format('Y-m-d') === $dataVal)) { $erros[] = 'Data de validade inválida (use YYYY-MM-DD).'; }

    if ($link !== '' && mb_strlen($link) > 45) { $erros[] = 'Link deve ter no máximo 45 caracteres.'; }

    if ($erros) {
      redirect_with('../PAGINAS_LOGISTA/banners_logista.html', ['erro_banner' => implode(' ', $erros)]);
    }

    // Monta UPDATE dinâmico (atualiza imagem só se uma nova foi enviada)
    $setSql = "descricao = :desc, data_validade = :dt, link = :lnk, CategoriasProdutos_id = :cat";
    if ($imgBlob !== null) {
      $setSql = "imagem = :img, " . $setSql;
    }

    $sql = "UPDATE Banners
              SET $setSql
            WHERE idBanners = :id";

    $st = $pdo->prepare($sql);

    if ($imgBlob !== null) {
      $st->bindValue(':img', $imgBlob, PDO::PARAM_LOB);
    }

    $st->bindValue(':desc', $descricao, PDO::PARAM_STR);
    $st->bindValue(':dt',   $dataVal,   PDO::PARAM_STR);

    if ($link === '') {
      $st->bindValue(':lnk', null, PDO::PARAM_NULL);
    } else {
      $st->bindValue(':lnk', $link, PDO::PARAM_STR);
    }

    if ($categoria === null) {
      $st->bindValue(':cat', null, PDO::PARAM_NULL);
    } else {
      $st->bindValue(':cat', $categoria, PDO::PARAM_INT);
    }

    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();

    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['editar_banner' => 'ok']);

  } catch (Throwable $e) {
    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_banner' => 'Erro ao editar: ' . $e->getMessage()]);
  }
}




/*  ============================EXCLUSÃO=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
  try {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
      redirect_with('../PAGINAS_LOGISTA/banners_logista.html', ['erro_login' => 'ID inválido para exclusão.']);
    }

    $st = $pdo->prepare("DELETE FROM Banners WHERE idBanners = :id");
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();

    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['excluir_login' => 'ok']);

  } catch (Throwable $e) {
    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_login' => 'Erro ao excluir: ' . $e->getMessage()]);
  }
}




     ]);
     /* Verificando se foi cadastrado no banco de dados */
     if($inserir){
        redirecWith("../index.html",
        ["cadastro" => "ok"]) ;
     }else{
        redirecWith("../paginas/cadastro.html",["erro" 
        =>"Erro ao cadastrar no banco de dados"]);
     }
    /* agora que tudo foi feito no Try, vamos elaborar 
    o catch com os possiveis erros */

}catch(PDOException $e){
     redirecWith("../paginas/cadastro.html",
      ["erro" => "Erro no banco de dados: "
      .$e->getMessage()]);
}


?>