
document.addEventListener("DOMContentLoaded", function () {

    // Ler os parâmetros enviados pela rota.php
    const parametros = new URLSearchParams(window.location.search);

    const quantidadeLinhas = Number(parametros.get("linhas"));

    // Elementos da tela
    const nomeOrigem = document.getElementById("nome-origem");
    const nomeDestino = document.getElementById("nome-destino");

    const tituloLinhas = document.getElementById("titulo-linhas");

    const primeiraLinha = document.getElementById("linha-1");
    const segundaLinha = document.getElementById("linha-2");
    const integracao = document.getElementById("integracao");

    // Receber os dados
    const origem = parametros.get("nome_origem");
    const destino = parametros.get("nome_destino");

    const numeroLinha1 = parametros.get("linha1");
    const corLinha1 = parametros.get("cor1");

    const numeroLinha2 = parametros.get("linha2");
    const corLinha2 = parametros.get("cor2");

    const pontoIntegracao = parametros.get("nome_conexao");

    // Verificar se os dados principais existem
    if (
        !origem ||
        !destino ||
        !numeroLinha1 ||
        !corLinha1 ||
        ![1, 2].includes(quantidadeLinhas)
    ) {
        document.querySelector(".resultado-card").innerHTML =
            "<h1>Não foi possível carregar o resultado.</h1>" +
            "<p>Os dados da viagem estão incompletos.</p>";

        return;
    }

    // Mostrar origem e destino
    nomeOrigem.textContent = origem;
    nomeDestino.textContent = destino;

    // Mostrar primeira linha
    document.getElementById("numero-linha-1").textContent = numeroLinha1;
    document.getElementById("cor-linha-1").textContent = corLinha1;

    if (quantidadeLinhas === 1) {

        // Resultado com apenas uma linha
        tituloLinhas.textContent = "Você precisará de uma linha";

        segundaLinha.style.display = "none";
        integracao.style.display = "none";

        primeiraLinha.querySelector("h3").textContent = "Linha utilizada";

    } else {

        // Verificar dados da segunda linha
        if (!numeroLinha2 || !corLinha2 || !pontoIntegracao) {

            document.querySelector(".resultado-card").innerHTML =
                "<h1>Não foi possível carregar o resultado.</h1>" +
                "<p>Os dados da integração estão incompletos.</p>";

            return;
        }

        // Resultado com duas linhas
        tituloLinhas.textContent = "Você precisará de duas linhas";

        document.getElementById("numero-linha-2").textContent = numeroLinha2;
        document.getElementById("cor-linha-2").textContent = corLinha2;

        document.getElementById("nome-integracao").textContent =
            pontoIntegracao;
    }

});
