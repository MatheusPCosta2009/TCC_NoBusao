
document.addEventListener("DOMContentLoaded", () => {

    const formulario = document.getElementById("form-linha");
    const api = formulario.dataset.api;

    const numero = document.getElementById("numero");
    const cor = document.getElementById("cor");

    const listaIda = document.getElementById("lista-ida");
    const listaVolta = document.getElementById("lista-volta");

    const botaoIda = document.getElementById("adicionar-ida");
    const botaoVolta = document.getElementById("adicionar-volta");

    const botaoCadastrar = document.getElementById("btn-cadastrar");
    const tabelaLinhas = document.getElementById("tabela-linhas");
    const mensagem = document.getElementById("mensagem");
    const contador = document.getElementById("contador");

    let pontosDisponiveis = [];

    async function enviarDados(dados) {

        const resposta = await fetch(api, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(dados)
        });

        const texto = await resposta.text();

        let resultado;

        try {
            resultado = JSON.parse(texto);
        } catch {
            throw new Error("O PHP não retornou JSON válido. Verifique linha.php.");
        }

        if (!resposta.ok || !resultado.sucesso) {
            throw new Error(resultado.mensagem || "Não foi possível concluir a operação.");
        }

        return resultado;
    }

    function mostrarMensagem(texto, tipo) {
        mensagem.textContent = texto;
        mensagem.className = tipo;
    }

    function criarHorario(container) {

        const item = document.createElement("div");
        item.className = "horario-item";

        const campo = document.createElement("input");
        campo.type = "time";
        campo.className = "campo-horario";
        campo.setAttribute("aria-label", "Horário do ponto");

        const excluir = document.createElement("button");
        excluir.type = "button";
        excluir.className = "botao-excluir";
        excluir.textContent = "Remover";

        excluir.addEventListener("click", () => item.remove());

        item.append(campo, excluir);
        container.appendChild(item);
    }

    function adicionarPonto(sentido) {

        const lista = sentido === "Ida" ? listaIda : listaVolta;

        const item = document.createElement("div");
        item.className = "ponto-item";

        const campoPonto = document.createElement("div");
        campoPonto.className = "campo-ponto";

        const select = document.createElement("select");
        select.className = "select-ponto";
        select.required = true;

        const opcaoInicial = document.createElement("option");
        opcaoInicial.value = "";
        opcaoInicial.textContent = "Selecione um ponto";

        select.appendChild(opcaoInicial);

        pontosDisponiveis.forEach(ponto => {

            const opcao = document.createElement("option");

            opcao.value = ponto.ID;
            opcao.textContent = ponto.Nome;

            select.appendChild(opcao);
        });

        campoPonto.appendChild(select);

        const listaHorarios = document.createElement("div");
        listaHorarios.className = "lista-horarios";

        const acoes = document.createElement("div");
        acoes.className = "acoes-ponto";

        const adicionarHorario = document.createElement("button");
        adicionarHorario.type = "button";
        adicionarHorario.className = "botao-horario";
        adicionarHorario.textContent = "+ Horário";

        adicionarHorario.addEventListener("click", () => {
            criarHorario(listaHorarios);
        });

        const excluirPonto = document.createElement("button");
        excluirPonto.type = "button";
        excluirPonto.className = "botao-excluir";
        excluirPonto.textContent = "Excluir ponto";

        excluirPonto.addEventListener("click", () => item.remove());

        acoes.append(adicionarHorario, excluirPonto);

        item.append(campoPonto, listaHorarios, acoes);

        lista.appendChild(item);

        criarHorario(listaHorarios);
    }

    function coletarPontos(lista, sentido) {

        const itens = lista.querySelectorAll(".ponto-item");

        return Array.from(itens).map((item, indice) => {

            const select = item.querySelector(".select-ponto");

            const horarios = Array.from(
                item.querySelectorAll(".campo-horario")
            )
                .map(campo => campo.value)
                .filter(hora => hora !== "");

            return {
                idPonto: Number(select.value),
                ordem: indice + 1,
                sentido: sentido,
                horarios: horarios
            };
        });
    }

    async function carregarPontos() {

        const resultado = await enviarDados({
            acao: "listar_pontos"
        });

        pontosDisponiveis = resultado.pontos || [];

        if (pontosDisponiveis.length === 0) {
            throw new Error("Nenhum ponto cadastrado. Cadastre os pontos antes de criar uma linha.");
        }

        botaoIda.disabled = false;
        botaoVolta.disabled = false;

        adicionarPonto("Ida");
        adicionarPonto("Volta");
    }

    async function carregarLinhas() {

        try {

            const resultado = await enviarDados({
                acao: "listar_linhas"
            });

            tabelaLinhas.innerHTML = "";

            const linhas = resultado.linhas || [];

            if (linhas.length === 0) {

                tabelaLinhas.innerHTML = `
                    <tr>
                        <td colspan="4">Nenhuma linha cadastrada.</td>
                    </tr>
                `;

            } else {

                linhas.forEach(linha => {

                    const tr = document.createElement("tr");

                    const tdNumero = document.createElement("td");
                    tdNumero.textContent = linha.ID;

                    const tdCor = document.createElement("td");
                    tdCor.textContent = linha.Cor;

                    const tdPontos = document.createElement("td");
                    tdPontos.textContent = linha.total_pontos;

                    const tdAcao = document.createElement("td");

                    const excluir = document.createElement("button");
                    excluir.type = "button";
                    excluir.className = "botao-excluir";
                    excluir.textContent = "Excluir";

                    excluir.addEventListener("click", () => {
                        excluirLinha(linha.ID);
                    });

                    tdAcao.appendChild(excluir);

                    tr.append(tdNumero, tdCor, tdPontos, tdAcao);

                    tabelaLinhas.appendChild(tr);
                });
            }

            contador.textContent =
                `${linhas.length} ${linhas.length === 1 ? "linha cadastrada" : "linhas cadastradas"}`;

        } catch (erro) {

            tabelaLinhas.innerHTML = `
                <tr>
                    <td colspan="4">Não foi possível carregar as linhas.</td>
                </tr>
            `;

            contador.textContent = erro.message;
        }
    }

    async function excluirLinha(id) {

        if (!confirm(`Deseja realmente excluir a linha ${id}?`)) {
            return;
        }

        try {

            await enviarDados({
                acao: "excluir",
                numero: id
            });

            mostrarMensagem("Linha excluída com sucesso.", "mensagem-sucesso");

            await carregarLinhas();

        } catch (erro) {

            mostrarMensagem(erro.message, "mensagem-erro");
        }
    }

    botaoIda.addEventListener("click", () => adicionarPonto("Ida"));
    botaoVolta.addEventListener("click", () => adicionarPonto("Volta"));

    formulario.addEventListener("submit", async (evento) => {

        evento.preventDefault();

        if (!formulario.reportValidity()) {
            return;
        }

        const pontosIda = coletarPontos(listaIda, "Ida");
        const pontosVolta = coletarPontos(listaVolta, "Volta");

        const numeroLinha = numero.value.trim();

        if (!/^[0-9]+$/.test(numeroLinha) || Number(numeroLinha) <= 0) {

            mostrarMensagem("Digite um número válido para a linha.", "mensagem-erro");
            return;
        }

        const todosPontos = [...pontosIda, ...pontosVolta];

        if (pontosIda.length === 0 || pontosVolta.length === 0) {

            mostrarMensagem("Adicione pelo menos um ponto na Ida e um na Volta.", "mensagem-erro");
            return;
        }

        if (todosPontos.some(ponto => !ponto.idPonto)) {

            mostrarMensagem("Selecione um ponto em cada campo.", "mensagem-erro");
            return;
        }

        botaoCadastrar.disabled = true;
        botaoCadastrar.textContent = "Cadastrando...";

        try {

            await enviarDados({
                acao: "cadastrar",
                numero: numeroLinha,
                cor: cor.value.trim(),
                pontos: todosPontos
            });

            mostrarMensagem("Linha cadastrada com sucesso!", "mensagem-sucesso");

            formulario.reset();

            listaIda.innerHTML = "";
            listaVolta.innerHTML = "";

            adicionarPonto("Ida");
            adicionarPonto("Volta");

            await carregarLinhas();

        } catch (erro) {

            mostrarMensagem(erro.message, "mensagem-erro");

        } finally {

            botaoCadastrar.disabled = false;
            botaoCadastrar.textContent = "Cadastrar linha";
        }
    });

    async function iniciar() {

        botaoIda.disabled = true;
        botaoVolta.disabled = true;

        try {

            await carregarLinhas();
            await carregarPontos();

        } catch (erro) {

            mostrarMensagem(erro.message, "mensagem-erro");
        }
    }

    iniciar();

});
