document.addEventListener('DOMContentLoaded', () => {
  const form  = document.getElementById('form-login');
  const cpfEl = document.getElementById('cpf');
  const senEl = document.getElementById('senha');
  const btn   = form.querySelector('button[type="submit"]');

  // Função simples para exibir mensagens (alert pode ser trocado por Toast Bootstrap)
  const showMsg = (msg, type = 'danger') => {
    // Criar um pequeno alerta Bootstrap (mais bonito que alert)
    const alertBox = document.createElement('div');
    alertBox.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertBox.style.zIndex = '9999';
    alertBox.innerHTML = `
      ${msg}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertBox);

    // Remove após 3 segundos
    setTimeout(() => {
      alertBox.remove();
    }, 3000);
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Remove tudo que não é número do CPF/CNPJ
    const cpfCnpj = (cpfEl.value || '').replace(/\D+/g, '').trim();
    const senha   = (senEl.value || '').trim();

    // Validação básica
    if (!cpfCnpj || !senha) {
      showMsg('⚠️ Preencha CPF/CNPJ e senha.', 'warning');
      return;
    }

    // Validação opcional de tamanho mínimo
    if (cpfCnpj.length < 11) {
      showMsg('⚠️ CPF/CNPJ inválido.', 'warning');
      return;
    }

    // Estado de carregamento
    btn.disabled = true;
    btn.textContent = 'Entrando...';

    try {
      const resp = await fetch('./PHP/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cpfCnpj, senha })
      });

      if (!resp.ok) throw new Error('Erro na resposta do servidor.');

      const data = await resp.json();

      if (data.ok) {
        showMsg('✅ Login realizado com sucesso!', 'success');
        setTimeout(() => {
          window.location.href = data.redirect || 'index.html';
        }, 1000);
      } else {
        showMsg(data.msg || 'Credenciais inválidas.', 'danger');
      }
    } catch (err) {
      console.error('Erro:', err);
      showMsg('❌ Erro de conexão com o servidor.', 'danger');
    } finally {
      btn.disabled = false;
      btn.textContent = 'Entrar';
    }
  });
});
