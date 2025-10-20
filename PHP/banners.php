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
        $imagem = readImageToBlob($_FILES["imagem"] ?? null);

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
