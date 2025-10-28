(function () {
  const esc = s => (s ?? "").toString().replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]));

  const placeholder = (w = 1200, h = 400, txt = "SEM IMAGEM") =>
    "data:image/svg+xml;base64," + btoa(
      `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}">
        <rect width="100%" height="100%" fill="#e9ecef"/>
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
              font-family="Arial, sans-serif" font-size="28" fill="#6c757d">${txt}</text>
      </svg>`
    );

  const hojeYMD = new Date().toISOString().slice(0,10);
  const dentroDaValidade = d => (!d ? true : d >= hojeYMD);

  function resolveImagemSrc(banner) {
    if (!banner?.imagem) return placeholder();
    return `data:image/jpeg;base64,${banner.imagem}`;
  }

  function renderErro(container, titulo, detalhesHtml) {
    container.innerHTML = `
      <div class="carousel-item active">
        <div class="p-3">
          <div class="alert alert-danger mb-2"><strong>${esc(titulo)}</strong></div>
          <div class="alert alert-light border small" style="white-space:pre-wrap">${detalhesHtml}</div>
        </div>
      </div>`;
    const ind = document.getElementById("banners-indicators");
    if (ind) ind.innerHTML = "";
  }

  function renderCarrossel(container, indicators, banners) {
    if (!Array.isArray(banners) || !banners.length) {
      renderErro(container, "Nenhum banner disponível.", "O servidor respondeu, mas a lista veio vazia.");
      return;
    }

    container.innerHTML = banners.map((b, i) => {
      const active = i === 0 ? "active" : "";
      const src = resolveImagemSrc(b);
      const desc = b.descricao ?? "Banner";
      const imgTag = `<img src="${src}" class="d-block w-100" alt="${esc(desc)}" loading="lazy" style="object-fit:cover; height:400px;">`;
      const wrapped = b.link ? `<a href="${esc(b.link)}" target="_blank">${imgTag}</a>` : imgTag;
      return `<div class="carousel-item ${active}">${wrapped}</div>`;
    }).join("");

    container.innerHTML = container.innerHTML;

    if (indicators) {
      indicators.innerHTML = banners.map((_, i) =>
        `<button type="button" data-bs-target="#carouselBanners" data-bs-slide-to="${i}" class="${i===0?"active":""}" aria-label="Slide ${i+1}"></button>`
      ).join("");
    }
  }

  async function listarBannersCarrossel({containerSelector = "#banners-home", indicatorsSelector = "#banners-indicators", urlCandidates = ["../PHP/banners.php?listar=1"], apenasValidos = true} = {}) {
    const container = document.querySelector(containerSelector);
    const indicators = document.querySelector(indicatorsSelector);
    if (!container) return;

    container.innerHTML = `<div class="carousel-item active"><div class="p-3 text-muted">Carregando banners…</div></div>`;
    if (indicators) indicators.innerHTML = "";

    let banners = null;
    for (const url of urlCandidates) {
      try {
        const resp = await fetch(url, { headers: { "Accept": "application/json" } });
        if (!resp.ok) continue;
        const data = await resp.json();
        if (data?.banners) { banners = data.banners; break; }
      } catch {}
    }

    if (!banners) {
      renderErro(container, "Não foi possível carregar os banners.", "• Verifique o caminho do PHP\n• Garanta header JSON no PHP\n• Retorne { ok:true, banners:[...] }");
      return;
    }

    if (apenasValidos) banners = banners.filter(b => dentroDaValidade(b.data_validade));
    renderCarrossel(container, indicators, banners);
  }

  document.addEventListener("DOMContentLoaded", () => {
    listarBannersCarrossel({
      urlCandidates: ["../PHP/banners.php?listar=1", "PHP/banners.php?listar=1", "../../PHP/banners.php?listar=1"],
      apenasValidos: true
    });
  });
})();
