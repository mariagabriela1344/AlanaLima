// =======================================
// Função para listar Formas de Pagamento
// =======================================
function listarFormasPagamento(tabelaPG) {
  document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById(tabelaPG);

    // URL correta do PHP (ajuste conforme o nome real do arquivo)
    const url = '../PHP/cadastro_formas_pagamento.php?listar=1&format=json';

    // Escapa caracteres especiais (segurança)
    const esc = s => (s || '').replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    // Gera o HTML de uma linha da tabela
    const row = f => `
      <tr>
        <td>${Number(f.id) || ''}</td>
        <td>${esc(f.nome || '-')}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning" data-id="${f.id}">
            <i class="bi bi-pencil"></i> Editar
          </button>
          <button class="btn btn-sm btn-danger" data-id="${f.id}">
            <i class="bi bi-trash"></i> Excluir
          </button>
        </td>
      </tr>`;

    // Faz a requisição ao PHP e preenche a tabela
    fetch(url, { cache: 'no-store' })
      .then(r => {
        if (!r.ok) throw new Error(`Erro HTTP: ${r.status}`);
        return r.json();
      })
      .then(d => {
        // Verifica se o retorno está correto
        if (!d.ok) throw new Error(d.error || 'Erro ao listar formas de pagamento');

        // Extrai o array de formas de pagamento (compatível com o PHP)
        const arr = d.formas_pagamento || [];

        // Preenche o corpo da tabela
        tbody.innerHTML = arr.length
          ? arr.map(row).join('')
          : `<tr><td colspan="3" class="text-center text-muted">Nenhuma forma de pagamento cadastrada.</td></tr>`;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  });
}

// =======================================
// Função para listar Fretes
// =======================================
function listarFretes(tabelaFt) {
  document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById(tabelaFt);
    const url = '../PHP/cadastro_frete.php?listar=1&format=json';

    const esc = s => (s || '').replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    const row = f => `
      <tr>
        <td>${Number(f.id) || ''}</td>
        <td>${esc(f.bairro || '-')}</td>
        <td>${esc(f.transportadora || '-')}</td>
        <td class="text-end">${moeda.format(parseFloat(f.valor ?? 0))}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning" data-id="${f.id}">
            <i class="bi bi-pencil"></i> Editar
          </button>
          <button class="btn btn-sm btn-danger" data-id="${f.id}">
            <i class="bi bi-trash"></i> Excluir
          </button>
        </td>
      </tr>`;

    fetch(url, { cache: 'no-store' })
      .then(r => {
        if (!r.ok) throw new Error(`Erro HTTP: ${r.status}`);
        return r.json();
      })
      .then(d => {
        if (!d.ok) throw new Error(d.error || 'Erro ao listar fretes');
        const fretes = d.fretes || [];
        tbody.innerHTML = fretes.length
          ? fretes.map(row).join('')
          : `<tr><td colspan="5" class="text-center text-muted">Nenhum frete cadastrado.</td></tr>`;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  });
}

// =======================================
// Execução das funções
// =======================================
listarFormasPagamento("tbPg");
listarFretes("tbFretes");
