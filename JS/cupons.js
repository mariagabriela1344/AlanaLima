function listarCupons(tabelaId) {
  document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById(tabelaId);
    const url = '../PHP/cupons.php?listar=1&format=json'; 

    // Função para escapar caracteres HTML (converte tudo para string primeiro)
    const esc = s => String(s || '').replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    // Função para criar linha da tabela
    const row = c => `
      <tr>
        <td>${esc(c.nome)}</td>
        <td>R$ ${parseFloat(c.valor).toFixed(2)}</td>
        <td>${esc(c.data_validade)}</td>
        <td>${esc(c.quantidade)}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning me-1" data-id="${c.id}">
            <i class="bi bi-pencil"></i> Editar
          </button>
          <button class="btn btn-sm btn-danger" data-id="${c.id}">
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
        if (!d.ok) throw new Error(d.error || 'Erro ao listar cupons');
        const cupons = d.cupons || [];
        tbody.innerHTML = cupons.length
          ? cupons.map(row).join('')
          : `<tr><td colspan="5" class="text-center text-muted">Nenhum cupom cadastrado.</td></tr>`;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  });
}


listarCupons("listCupom");