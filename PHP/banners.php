<?php
require_once __DIR__ . "/conexao.php";

function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}


// ===========================
// LISTAR BANNERS (GET)
// ===========================

// ==============================================
// FUNÇÃO PARA LER IMAGEM COMO BLOB
// ==============================================
function read_image_to_blob(?array $file): ?string {
  if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
  $bin = file_get_contents($file['tmp_name']);
  return $bin === false ? null : $bin;
}

// ==============================================
// LISTAGEM DE BANNERS
// ==============================================
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        // Consulta a tabela correta e campos corretos
        $stmt = $pdo->query("
            SELECT idBanners, imagem, data_validade, descricao, link, CategoriasProdutos_id 
            FROM Banners 
            ORDER BY idBanners DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $banners = array_map(function ($r) {
            return [
                'idBanners'   => (int)$r['idBanners'],
                'imagem'      => !empty($r['imagem']) ? base64_encode($r['imagem']) : null,
                'data_validade' => $r['data_validade'],
                'descricao'   => $r['descricao'],
                'link'        => $r['link'],
                'categoria_id'=> $r['CategoriasProdutos_id']
            ];
        }, $rows);

        echo json_encode([
            'ok' => true,
            'count' => count($banners),
            'banners' => $banners
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }

    exit;
}




/*  ============================ATUALIZAÇÃO=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
  try {
    $id        = (int)($_POST['id'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $dataVal   = trim($_POST['data_validade'] ?? '');
    $link      = trim($_POST['link'] ?? '');
    $categoria = $_POST['categoriaproduto'] ?? null;
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
      redirect_with("../paginaslogista/promocoes_logista.html", ['erro_banner' => implode(' ', $erros)]);
    }

    // Monta UPDATE dinâmico (atualiza imagem só se uma nova foi enviada)
    $setSql = "descricao = :desc, data_validade = :dt, link = :lnk, CategoriasProdutos_id = :cat";
    if ($imgBlob !== null) {
      $setSql = "imagem = :img, " . $setSql;
    }

    $sql = "UPDATE Banners
              SET $setSql
            WHERE idBanners = :idBanners";

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

    redirect_with("../paginaslogista/promocoes_logista.html", ['editar_banner' => 'ok']);

  } catch (Throwable $e) {
    redirect_with("../paginaslogista/promocoes_logista.html", ['erro_banner' => 'Erro ao editar: ' . $e->getMessage()]);
  }
}



/*  ============================EXCLUSÃO=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
  try {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
      redirect_with("../paginaslogista/promocoes_logista.html", ['erro_banner' => 'ID inválido para exclusão.']);
    }

    $st = $pdo->prepare("DELETE FROM Banners WHERE idBanners = :id");
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();

    redirect_with("../paginaslogista/promocoes_logista.html", ['excluir_banner' => 'ok']);

  } catch (Throwable $e) {
    redirect_with("../paginaslogista/promocoes_logista.html", ['erro_banner' => 'Erro ao excluir: ' . $e->getMessage()]);
  }
}







// ===========================
// CADASTRAR / EXCLUIR (POST)
// ===========================
try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginaslogista/promocoes_logista.html", ["erro" => "Método inválido"]);
    }

    

    
        $descricao = trim($_POST["descricao"] ?? "");
        $link = trim($_POST["link"] ?? "");
        $data_validade = $_POST["data_validade"] ?? "";
        $categoria = $_POST["categoria"] ?? null;
        $imagem = null;

if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] === UPLOAD_ERR_OK) {
    $imagem = file_get_contents($_FILES["imagem"]["tmp_name"]);
}


        if ($descricao === "" || !$imagem || $data_validade === "") {
            redirecWith("../paginaslogista/promocoes_logista.html", ["erro" => "Preencha todos os campos obrigatórios."]);
        }

        $sql = "INSERT INTO Banners (imagem, data_validade, descricao, link, CategoriasProdutos_id)
                VALUES (:img, :data, :desc, :link, :cat)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":img" => $imagem,
            ":data" => $data_validade,
            ":desc" => $descricao,
            ":link" => $link,
            ":cat" => $categoria
        ]);

        redirecWith("../paginaslogista/promocoes_logista.html", ["cadastro" => "ok"]);
    

    if ($acao === "excluir") {
        $id = (int)($_POST["id"] ?? 0);
        if ($id <= 0) {
            redirecWith("../paginaslogista/promocoes_logista.html", ["erro" => "ID inválido."]);
        }

        $stmt = $pdo->prepare("DELETE FROM Banners WHERE idBanners = ?");
        $stmt->execute([$id]);

        redirecWith("../paginaslogista/promocoes_logista.html", ["excluir" => "ok"]);
    }



  

}catch (Exception $e) {
    redirecWith("../paginaslogista/promocoes_logista.html", ["erro" => "Erro: " . $e->getMessage()]);
}




?>
