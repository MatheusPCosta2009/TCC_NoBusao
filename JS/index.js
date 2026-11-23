
document.addEventListener("DOMContentLoaded", () => {

    // ==========================================
    // ELEMENTOS DO HTML
    // ==========================================

    const bairroOrigem = document.getElementById("bairro_origem");
    const pontoOrigem = document.getElementById("ponto_origem");

    const bairroDestino = document.getElementById("bairro_destino");
    const pontoDestino = document.getElementById("ponto_destino");


    // ==========================================
    // CAMINHOS DOS ARQUIVOS PHP
    // ==========================================

    const URL_BAIRROS = "PHP/buscar_bairros.php";
    const URL_PONTOS = "PHP/buscar_pontos.php";


    // ==========================================
    // FUNÇÃO PARA CONSULTAR O PHP
    // ==========================================

    async function consultarPHP(url) {

        const resposta = await fetch(url, {
            method: "GET",
            cache: "no-store"
        });

        const texto = await resposta.text();

        console.log("URL consultada:", resposta.url);
        console.log("Status HTTP:", resposta.status);
        console.log("Resposta recebida:", texto);

        if (!resposta.ok) {
            throw new Error(
                "Erro HTTP " + resposta.status + ": " + texto
            );
        }

        try {

            return JSON.parse(texto);

        } catch (erro) {

            console.error("Resposta inválida do PHP:", texto);

            throw new Error("O PHP não retornou um JSON válido.");

        }

    }


    // ==========================================
    // CARREGAR BAIRROS
    // ==========================================

    async function carregarBairros() {

        bairroOrigem.disabled = true;
        bairroDestino.disabled = true;

        bairroOrigem.innerHTML =
            '<option value="">Carregando bairros...</option>';

        bairroDestino.innerHTML =
            '<option value="">Carregando bairros...</option>';

        try {

            const bairros = await consultarPHP(URL_BAIRROS);

            if (!Array.isArray(bairros)) {
                throw new Error("O retorno dos bairros não é uma lista.");
            }

            if (bairros.length === 0) {
                throw new Error("Nenhum bairro cadastrado no banco.");
            }

            // Preencher os dois seletores

            [bairroOrigem, bairroDestino].forEach(select => {

                select.innerHTML =
                    '<option value="">Selecione um bairro</option>';

                bairros.forEach(bairro => {

                    const opcao = document.createElement("option");

                    opcao.value = bairro.ID;
                    opcao.textContent = bairro.Nome;

                    select.appendChild(opcao);

                });

                select.disabled = false;

            });

            console.log("Bairros carregados:", bairros);

        } catch (erro) {

            console.error("Erro ao carregar bairros:", erro);

            bairroOrigem.innerHTML =
                '<option value="">Erro ao carregar bairros</option>';

            bairroDestino.innerHTML =
                '<option value="">Erro ao carregar bairros</option>';

        }

    }


    // ==========================================
    // CARREGAR PONTOS DO BAIRRO
    // ==========================================

    async function carregarPontos(idBairro, selectPonto) {

        selectPonto.disabled = true;

        if (!idBairro) {

            selectPonto.innerHTML =
                '<option value="">Selecione primeiro o bairro</option>';

            return;

        }

        selectPonto.innerHTML =
            '<option value="">Carregando pontos...</option>';

        try {

            const url = URL_PONTOS +
                "?id_bairro=" + encodeURIComponent(idBairro);

            const pontos = await consultarPHP(url);

            if (!Array.isArray(pontos)) {
                throw new Error("O retorno dos pontos não é uma lista.");
            }

            selectPonto.innerHTML =
                '<option value="">Selecione um ponto</option>';

            if (pontos.length === 0) {

                selectPonto.innerHTML =
                    '<option value="">Nenhum ponto cadastrado neste bairro</option>';

                return;

            }

            pontos.forEach(ponto => {

                const opcao = document.createElement("option");

                opcao.value = ponto.ID;
                opcao.textContent = ponto.Nome;

                selectPonto.appendChild(opcao);

            });

            selectPonto.disabled = false;

            console.log("Pontos carregados:", pontos);

        } catch (erro) {

            console.error("Erro ao carregar pontos:", erro);

            selectPonto.innerHTML =
                '<option value="">Erro ao carregar pontos</option>';

        }

    }


    // ==========================================
    // EVENTO: BAIRRO DE ORIGEM
    // ==========================================

    bairroOrigem.addEventListener("change", () => {

        carregarPontos(
            bairroOrigem.value,
            pontoOrigem
        );

    });


    // ==========================================
    // EVENTO: BAIRRO DE DESTINO
    // ==========================================

    bairroDestino.addEventListener("change", () => {

        carregarPontos(
            bairroDestino.value,
            pontoDestino
        );

    });


    // ==========================================
    // INICIAR SISTEMA
    // ==========================================

    carregarBairros();

});
