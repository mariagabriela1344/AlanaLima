function listarcategoria(nomeid){

    (async () => {
    // selecionando o elemento html da tela de cadastro de produtos
    const sel = document.querySelector(nomeid);
    try {
        // criando a váriavel que guardar os dados vindo do php, que estão no metodo de listar
        const r = await fetch("../PHP/cadastro_categorias.php?listar=1");
        // se o retorno do php vier false, significa que não foi possivel listar os dados
        if (!r.ok) throw new Error("Falha ao listar categorias!");
        /* se vier dados do php, ele joga as 
        informações dentro do campo html em formato de texto
        innerHTML- inserir dados em elementos html
        */
        sel.innerHTML = await r.text();
    } catch (e) {
        // se dê erro na listagem, aparece Erro ao carregar dentro do campo html
        sel.innerHTML = "<option disable>Erro ao carregar</option>"
    }
})();
}

// ===============================
// FUNÇÃO DE LISTAGEM DE BANNERS
// ===============================
// Função para listar banners e exibir as imagens
function listarBanners(tabelaId) {
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById(tabelaId);
        const url = '../PHP/banners.php?listar=1&format=json'; // ajuste o caminho

        // Função para escapar caracteres HTML
        const esc = s => (s || '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));

        // Função para criar linha da tabela
        const row = b => `
            <tr>
                <td>
                    ${b.imagem ? `<img src="data:image/jpeg;base64,${b.imagem}" style="width:100px; height:auto;">` : ''}
                </td>
                <td>${esc(b.descricao || '-')}</td>
                <td>${esc(b.link || '-')}</td>
                <td>${esc(b.categoria_id ?? '-')}</td>
                <td>${esc(b.data_validade || '-')}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-warning me-1" data-id="${b.idBanners}">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <button class="btn btn-sm btn-danger" data-id="${b.idBanners}">
                        <i class="bi bi-trash"></i> Excluir
                    </button>
                </td>
            </tr>
        `;

        // Requisição fetch
        fetch(url, { cache: 'no-store' })
            .then(r => {
                if (!r.ok) throw new Error(`Erro HTTP: ${r.status}`);
                return r.json();
            })
            .then(d => {
                if (!d.ok) throw new Error(d.error || 'Erro ao listar banners');
                const banners = d.banners || [];
                tbody.innerHTML = banners.length
                    ? banners.map(row).join('')
                    : `<tr><td colspan="6" class="text-center text-muted">Nenhum banner cadastrado.</td></tr>`;
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
            });
    });
}

// =======================================
// Execução
// =======================================
listarBanners("listbanners");



listarcategoria("#catbanner");
listarcategoria("#catpromo");