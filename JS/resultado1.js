
/* ==========================================
   RESULTADO DIRETO - NoBusão
========================================== */


/* ==========================================
   RECEBER DADOS DA PESQUISA
========================================== */

const parametros = new URLSearchParams(window.location.search);

const origem = parametros.get("origem");
const destino = parametros.get("destino");


/* ==========================================
   ELEMENTOS DO HTML
========================================== */

const pontos = document.querySelectorAll(".trajeto .local h2");

const numeroLinha = document.querySelector(".identificacao-linha h3");

const corLinha = document.querySelector(".cor-linha");

const horariosElemento = document.querySelector(
    ".detalhes-linha div:first-child p"
);

const mapa = document.querySelector("#mapa");

const titulo = document.querySelector(".titulo h1");

const descricao = document.querySelector(".titulo p");


/* ==========================================
   BUSCAR RESULTADO NO PHP
========================================== */

async function buscarResultado() {

    if (!origem || !destino) {
        mostrarErro("Não recebemos os pontos da pesquisa.");
        return;
    }

    try {

        const resposta = await fetch(
            `../PHP/resultado1.php?origem=${encodeURIComponent(origem)}&destino=${encodeURIComponent(destino)}`
        );

        const dados = await resposta.json();

        if (!resposta.ok || !dados.sucesso) {
            throw new Error(dados.mensagem || "Não foi possível encontrar o trajeto.");
        }

        preencherResultado(dados);

    } catch (erro) {

        mostrarErro(erro.message);

        console.error("Erro ao buscar resultado:", erro);

    }

}


/* ==========================================
   PREENCHER INFORMAÇÕES DA TELA
========================================== */

function preencherResultado(dados) {

    titulo.textContent = "Encontramos uma linha para o seu trajeto!";

    descricao.textContent =
        "Confira as informações da linha que realiza seu percurso.";

    // Origem e destino

    pontos[0].textContent = dados.origem;
    pontos[1].textContent = dados.destino;


    // Identificação da linha

    const id = String(dados.linha.id).padStart(2, "0");

    numeroLinha.textContent = `Linha ${id}`;


    // Cor da linha

    const cor = dados.linha.cor;

    if (CSS.supports("color", cor)) {
        corLinha.style.backgroundColor = cor;
    }


    // Horários

    if (dados.horarios.length > 0) {

        horariosElemento.textContent = dados.horarios
            .map(horario => horario.substring(0, 5))
            .join(" • ");

    } else {

        horariosElemento.textContent = "Horários não cadastrados";

    }


    // Mapa

    const mensagemMapa = mapa.querySelector(".mapa-mensagem");

    if (mensagemMapa) {

        mensagemMapa.querySelector("h3").textContent =
            "Trajeto encontrado";

        mensagemMapa.querySelector("p").textContent =
            `${dados.pontos.length} pontos encontrados no percurso.`;

    }

}


/* ==========================================
   MOSTRAR ERROS
========================================== */

function mostrarErro(mensagem) {

    titulo.textContent = "Não foi possível encontrar o trajeto.";

    descricao.textContent = mensagem;

    if (mapa) {

        mapa.innerHTML = `
            <div class="mapa-mensagem">
                <h3>Trajeto indisponível</h3>
                <p>${mensagem}</p>
            </div>
        `;

    }

}


/* ==========================================
   INICIAR
========================================== */

buscarResultado();
