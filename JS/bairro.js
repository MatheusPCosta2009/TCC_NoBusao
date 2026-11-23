
const formulario = document.getElementById("form-bairro");
const campoNome = document.getElementById("nome");
const listaBairros = document.getElementById("lista-bairros");
const mensagem = document.getElementById("mensagem");
const totalBairros = document.getElementById("total-bairros");

const API = "../PHP/bairros.php";

// CARREGAR BAIRROS

async function carregarBairros() {

    try {

        const resposta = await fetch(API);
        const bairros = await resposta.json();

        if (!resposta.ok || !Array.isArray(bairros)) {
            throw new Error("Não foi possível carregar os bairros.");
        }

        listaBairros.innerHTML = "";

        totalBairros.textContent =
            `${bairros.length} ${bairros.length === 1 ? "bairro" : "bairros"}`;

        if (bairros.length === 0) {

            listaBairros.innerHTML = `
                <tr>
                    <td colspan="3">Nenhum bairro cadastrado.</td>
                </tr>
            `;

            return;
        }

        bairros.forEach(bairro => {

            const linha = document.createElement("tr");

            const colunaID = document.createElement("td");
            colunaID.textContent = bairro.id;

            const colunaNome = document.createElement("td");
            colunaNome.textContent = bairro.nome;

            const colunaAcao = document.createElement("td");

            const botaoExcluir = document.createElement("button");

            botaoExcluir.textContent = "Excluir";
            botaoExcluir.classList.add("btn-excluir");

            botaoExcluir.addEventListener("click", () => {
                excluirBairro(bairro.id);
            });

            colunaAcao.appendChild(botaoExcluir);

            linha.appendChild(colunaID);
            linha.appendChild(colunaNome);
            linha.appendChild(colunaAcao);

            listaBairros.appendChild(linha);

        });

    } catch (erro) {

        console.error("Erro:", erro);

        listaBairros.innerHTML = `
            <tr>
                <td colspan="3">Erro ao carregar bairros.</td>
            </tr>
        `;

    }
}

// CADASTRAR BAIRRO

formulario.addEventListener("submit", async function(evento) {

    evento.preventDefault();

    const nome = campoNome.value.trim();

    if (nome === "") {
        mostrarMensagem("Digite o nome do bairro.", false);
        return;
    }

    try {

        const resposta = await fetch(API, {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                nome: nome
            })

        });

        const resultado = await resposta.json();

        if (!resposta.ok || !resultado.sucesso) {

            mostrarMensagem(resultado.mensagem, false);
            return;

        }

        mostrarMensagem(resultado.mensagem, true);

        formulario.reset();

        carregarBairros();

    } catch (erro) {

        console.error(erro);

        mostrarMensagem("Erro ao cadastrar o bairro.", false);

    }

});

// EXCLUIR BAIRRO

async function excluirBairro(id) {

    const confirmar = confirm(
        "Tem certeza de que deseja excluir este bairro?"
    );

    if (!confirmar) {
        return;
    }

    try {

        const resposta = await fetch(`${API}?id=${id}`, {
            method: "DELETE"
        });

        const resultado = await resposta.json();

        if (!resposta.ok || !resultado.sucesso) {

            mostrarMensagem(resultado.mensagem, false);
            return;

        }

        mostrarMensagem(resultado.mensagem, true);

        carregarBairros();

    } catch (erro) {

        console.error(erro);

        mostrarMensagem("Erro ao excluir o bairro.", false);

    }

}

// MENSAGENS

function mostrarMensagem(texto, sucesso) {

    mensagem.textContent = texto;

    mensagem.className = sucesso
        ? "mensagem-sucesso"
        : "mensagem-erro";

}

// INICIALIZAÇÃO

carregarBairros();
