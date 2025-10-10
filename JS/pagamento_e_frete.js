// Verifica se já existe lista no localStorage
let formasPagamento = JSON.parse(localStorage.getItem("formasPagamento")) || [];

// Função para cadastrar nova forma de pagamento
function cadastrarFormaPagamento(nome, tipo, detalhes) {
  const novaForma = {
    id: Date.now(),
    nome,       // Ex: "Cartão de Crédito"
    tipo,       // Ex: "Cartão", "PIX", "Boleto"
    detalhes,   // Ex: "Visa, Mastercard, etc."
  };

  formasPagamento.push(novaForma);
  localStorage.setItem("formasPagamento", JSON.stringify(formasPagamento));
  console.log(`✅ Forma de pagamento "${nome}" cadastrada com sucesso!`);
}

// Função para listar todas as formas cadastradas
function listarFormasPagamento() {
  console.log("📋 Formas de Pagamento Cadastradas:");
  formasPagamento.forEach((forma) => {
    console.log(`- ${forma.nome} (${forma.tipo}) - ${forma.detalhes}`);
  });
  return formasPagamento;
}

// Função para remover uma forma pelo ID
function removerFormaPagamento(id) {
  formasPagamento = formasPagamento.filter((forma) => forma.id !== id);
  localStorage.setItem("formasPagamento", JSON.stringify(formasPagamento));
  console.log(`❌ Forma de pagamento com ID ${id} removida.`);
}

// Exemplo de uso:
cadastrarFormaPagamento("PIX", "Transferência", "Chave: email@exemplo.com");
cadastrarFormaPagamento("Cartão de Crédito", "Cartão", "Visa, Mastercard");
cadastrarFormaPagamento("Boleto Bancário", "Boleto", "Pagamento em até 3 dias úteis");

listarFormasPagamento();

// Para remover uma forma específica, use o ID retornado:
if (formasPagamento.length > 0) {
  removerFormaPagamento(formasPagamento[0].id);
}





(async () => {
    // selecionando o elemento html da tela de cadastro de produtos
    const sel = document.querySelector("#pFrete");
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