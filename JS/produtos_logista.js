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

function listarProdutos(tabelaClasse) {
  document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.querySelector(`.${tabelaClasse} tbody`);
    const url = '../PHP/cadastro_produtos.php?listar=1&format=json'; // Ajuste para o caminho correto

    const esc = s => String(s || '').replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    const row = p => {
      // Pegar primeira imagem se existir
      const imgHtml = p.imagens && p.imagens.length
        ? `<img src="data:image/jpeg;base64,${p.imagens[0]}" style="width:72px; height:72px; object-fit:cover;" class="rounded">`
        : '';

      // Categorias como badges
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
            <button class="btn btn-sm btn-warning me-1" data-id="${p.id}"><i class="bi bi-pencil"></i> Editar</button>
            <button class="btn btn-sm btn-danger" data-id="${p.id}"><i class="bi bi-trash"></i> Excluir</button>
          </td>
        </tr>
      `;
    };

    fetch(url, { cache: 'no-store' })
      .then(r => {
        if (!r.ok) throw new Error(`Erro HTTP: ${r.status}`);
        return r.json();
      })
      .then(d => {
        if (!d.ok) throw new Error(d.error || 'Erro ao listar produtos');
        const produtos = d.produtos || [];
        tbody.innerHTML = produtos.length
          ? produtos.map(row).join('')
          : `<tr><td colspan="9" class="text-center text-muted">Nenhum produto cadastrado.</td></tr>`;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  });
}

listarcategoria("#pCategoria");
listarcategoria("#prodcat");
listarProdutos("#listprod");



// --- util 1) esc(): escapa caracteres especiais no texto (evita quebrar o HTML)
   const esc = s => (s||'').replace(/[&<>"']/g, c => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[c]));
 
  // --- util 2) ph(): gera um SVG base64 com as iniciais, usado quando não há imagem
  const ph  = n => 'data:image/svg+xml;base64,' + btoa(
    `<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60">
       <rect width="100%" height="100%" fill="#eee"/>
       <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
             font-family="sans-serif" font-size="12" fill="#999">
         ${(n||'?').slice(0,2).toUpperCase()}
       </text>
     </svg>`
  );
 
 
 
 
 const row = m => `
    <tr>
      <td>
        <img
          src="${m.imagem ? `data:${m.mime||'image/jpeg'};base64,${m.imagem}`
          : ph(m.nome)}"
          alt="${esc(m.nome||'Marca')}"
          style="width:60px;height:60px;object-fit:cover;border-radius:8px">
      </td>
      <td>${esc(m.nome||'-')}</td>
      <td class="text-end">
        <button class="btn btn-sm btn-warning" data-id="${m.idMarcas}">Editar</button>
        <button class="btn btn-sm btn-danger"  data-id="${m.idMarcas}">Excluir</button>
      </td>
    </tr>`;