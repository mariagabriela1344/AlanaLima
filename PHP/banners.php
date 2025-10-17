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

function readImageToBlob(?array $file): ?string {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;
    return file_get_contents($file['tmp_name']);
}

// ===========================
// LISTAR BANNERS (GET)
// ===========================
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    try {
        $sql = "
            SELECT 
                b.idBanners,
                b.descricao,
                b.link,
                b.data_validade,
                b.CategoriasProdutos_id,
                c.nome AS categoria
            FROM Banners b
            LEFT JOIN categorias_produtos c 
                ON c.idCategoriaProduto = b.CategoriasProdutos_id
            ORDER BY b.idBanners DESC
        ";
        $stmt = $pdo->query($sql);
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(["ok" => true, "banners" => $banners], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $e) {
        header("Content-Type: application/json; charset=utf-8", true, 500);
        echo json_encode(["ok" => false, "erro" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
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
