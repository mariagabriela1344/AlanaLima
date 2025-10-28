document.addEventListener('DOMContentLoaded', () => {
  const form  = document.getElementById('form-login');
  const cpfEl = document.getElementById('cnpj');  // seu input de CPF/CNPJ
  const senEl = document.getElementById('senha');

  const showMsg = (msg) => {
    alert(msg); // pode trocar por toast do Bootstrap se quiser
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Remove tudo que não é número do CPF/CNPJ
    const cpfCnpj = (cpfEl.value || '').replace(/\D+/g, '').trim();
    const senha   = (senEl.value || '').trim();

    if (!cpfCnpj || !senha) {
      showMsg('Preencha CPF/CNPJ e senha.');
      return;
    }

    try {
      const resp = await fetch('../PHP/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cnpj: cpfCnpj, senha: senha }) // corrigido: "senha" minúsculo
      });

      const data = await resp.json();

      if (data.ok) {
        window.location.href = data.redirect;
      } else {
        showMsg(data.msg || 'Credenciais inválidas.');
      }
    } catch (err) {
      showMsg('Erro de conexão com o servidor.');
    }
  });
});
