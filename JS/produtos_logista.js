// --- Função utilitária: escapa caracteres especiais
const esc = s => String(s || '').replace(/[&<>"']/g, c => ({
  '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'
}[c]));

// --- Função utilitária: placeholder SVG para imagens ausentes
const ph = n => 'data:image/svg+xml;base64,' + btoa(
  `<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60">
     <rect width="100%" height="100%" fill="#eee"/>
     <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
           font-family="sans-serif" font-size="12" fill="#999">
       ${(n||'?').slice(0,2).toUpperCase()}
     </text>
   </svg>`
);

// --- Listar categorias e inserir em select
function listarcategoria(seletor) {
  (async () => {
    const sel = document.querySelector(seletor);
    try {
      const r = await fetch("../PHP/cadastro_categorias.php?listar=1");
      if (!r.ok) throw new Error("Falha ao listar categorias!");
      sel.innerHTML = await r.text();
    } catch (e) {
      sel.innerHTML = "<option disabled>Erro ao carregar</option>";
    }
  })();
}

// --- Gerar linha de produto
const rowProduto = p => {
  const imgHtml = p.imagens && p.imagens.length
    ? `<img src="data:image/jpeg;base64,${p.imagens[0]}" style="width:72px; height:72px; object-fit:cover;" class="rounded">`
    : ph(p.nome);

  const catsHtml = (p.categorias || []).map(c => `<span class="badge text-bg-light me-1">${esc(c)}</span>`).join('');

  return `
    <tr>
      <td>${esc(p.id)}</td>
      <td><div class="prod-thumb rounded border d-flex align-items-center justify-content-center">${imgHtml}</div></td>
      <td>${esc(p.nome)}</td>
      <td>${catsHtml}</td>
      <td class="text-end">${esc(p.quantidade)}</td>
      <td class="text-end">R$ ${parseFloat(p.preco).toFixed(2)}</td>
      <td class="text-end">${p.preco_promocional ? `R$ ${parseFloat(p.preco_promocional).toFixed(2)}` : '—'}</td>
      <td>${esc(p.codigo)}</td>
      <td class="text-end">
        <button class="btn btn-sm btn-warning me-1" data-id="${p.id}">Editar</button>
        <button class="btn btn-sm btn-danger" data-id="${p.id}">Excluir</button>
      </td>
    </tr>
  `;
};

// --- Listar produtos e inserir no tbody
function listarProdutos(tbodySeletor) {
  const tbody = document.querySelector(tbodySeletor);
  const url = '../PHP/cadastro_produtos.php?listar=1&format=json';

  fetch(url, { cache: 'no-store' })
    .then(r => {
      if (!r.ok) throw new Error(`Erro HTTP: ${r.status}`);
      return r.json();
    })
    .then(d => {
      if (!d.ok) throw new Error(d.error || 'Erro ao listar produtos');
      const produtos = d.produtos || [];
      tbody.innerHTML = produtos.length
        ? produtos.map(rowProduto).join('')
        : `<tr><td colspan="9" class="text-center text-muted">Nenhum produto cadastrado.</td></tr>`;
    })
    .catch(err => {
      tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
    });
}

// --- Executa após o DOM estar carregado
document.addEventListener('DOMContentLoaded', () => {
  listarcategoria("#pCategoria");
  listarcategoria("#prodcat");
  listarProdutos("#listprod");
});
