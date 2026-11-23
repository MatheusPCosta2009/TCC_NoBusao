
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('form-ponto');
    const botao = document.getElementById('btn-cadastrar');

    carregarBairros();
    carregarPontos();

    /* ==========================================
       CADASTRAR PONTO
    ========================================== */

    form.addEventListener('submit', async (e) => {

        e.preventDefault();

        const nome = document.getElementById('nome').value.trim();
        const idBairro = document.getElementById('bairro').value;

        if (nome === '') {
            alert('Informe o nome do ponto.');
            return;
        }

        if (idBairro === '') {
            alert('Selecione um bairro.');
            return;
        }

        botao.disabled = true;
        botao.textContent = 'Cadastrando...';

        try {

            const resposta = await fetch('../PHP/pontos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nome: nome,
                    id_bairro: idBairro
                })
            });

            const dados = await lerResposta(resposta);

            if (!resposta.ok || !dados.sucesso) {
                throw new Error(
                    dados.mensagem || 'Erro ao cadastrar ponto.'
                );
            }

            alert(dados.mensagem);

            form.reset();

            await carregarPontos();

        } catch (erro) {

            console.error('Erro ao cadastrar ponto:', erro);
            alert(erro.message);

        } finally {

            botao.disabled = false;
            botao.textContent = 'Cadastrar ponto';

        }

    });

});


/* ==========================================
   LER RESPOSTA DO PHP
========================================== */

async function lerResposta(resposta) {

    const texto = await resposta.text();

    try {

        return JSON.parse(texto);

    } catch (erro) {

        console.error('Resposta recebida do PHP:', texto);

        throw new Error(
            'O PHP retornou uma resposta inválida. Verifique o console do navegador.'
        );

    }

}


/* ==========================================
   CARREGAR BAIRROS
========================================== */

async function carregarBairros() {

    const select = document.getElementById('bairro');

    select.innerHTML = '<option value="">Carregando bairros...</option>';

    try {

        const resposta = await fetch('../PHP/bairros.php');

        const bairros = await lerResposta(resposta);

        if (!resposta.ok || !Array.isArray(bairros)) {
            throw new Error('Não foi possível carregar os bairros.');
        }

        select.innerHTML = '<option value="">Selecione um bairro</option>';

        if (bairros.length === 0) {

            select.innerHTML = '<option value="">Nenhum bairro cadastrado</option>';

            return;
        }

        bairros.forEach(bairro => {

            const opcao = document.createElement('option');

            // CORRIGIDO: nomes iguais aos retornados pelo bairros.php
            opcao.value = bairro.id;
            opcao.textContent = bairro.nome;

            select.appendChild(opcao);

        });

    } catch (erro) {

        console.error('Erro ao carregar bairros:', erro);

        select.innerHTML = '<option value="">Erro ao carregar bairros</option>';

        alert('Não foi possível carregar os bairros. Verifique o cadastro de bairros.');

    }

}


/* ==========================================
   CARREGAR PONTOS
========================================== */

async function carregarPontos() {

    const lista = document.getElementById('lista-pontos');
    const contador = document.getElementById('contador');

    try {

        const resposta = await fetch('../PHP/pontos.php');

        const pontos = await lerResposta(resposta);

        if (!resposta.ok || !Array.isArray(pontos)) {
            throw new Error('Erro ao buscar pontos.');
        }

        lista.innerHTML = '';

        if (pontos.length === 0) {

            lista.innerHTML = `
                <tr>
                    <td colspan="4">
                        Nenhum ponto cadastrado.
                    </td>
                </tr>
            `;

        } else {

            pontos.forEach(ponto => {

                const linha = document.createElement('tr');

                const id = document.createElement('td');
                id.textContent = ponto.ID;

                const nome = document.createElement('td');
                nome.textContent = ponto.Nome;

                const bairro = document.createElement('td');
                bairro.textContent = ponto.Bairro;

                const acoes = document.createElement('td');

                const botaoExcluir = document.createElement('button');

                botaoExcluir.textContent = 'Excluir';
                botaoExcluir.classList.add('botao-excluir');

                botaoExcluir.addEventListener('click', () => {
                    excluirPonto(ponto.ID, botaoExcluir);
                });

                acoes.appendChild(botaoExcluir);

                linha.appendChild(id);
                linha.appendChild(nome);
                linha.appendChild(bairro);
                linha.appendChild(acoes);

                lista.appendChild(linha);

            });

        }

        contador.textContent =
            pontos.length + (pontos.length === 1 ? ' ponto' : ' pontos');

    } catch (erro) {

        console.error('Erro ao carregar pontos:', erro);

        lista.innerHTML = `
            <tr>
                <td colspan="4">
                    Erro ao carregar os pontos.
                </td>
            </tr>
        `;

        contador.textContent = 'Erro';

    }

}


/* ==========================================
   EXCLUIR PONTO
========================================== */

async function excluirPonto(id, botao) {

    const confirmar = confirm(
        'Tem certeza que deseja excluir este ponto?'
    );

    if (!confirmar) {
        return;
    }

    botao.disabled = true;

    try {

        const resposta = await fetch('../PHP/pontos.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        });

        const dados = await lerResposta(resposta);

        if (!resposta.ok || !dados.sucesso) {
            throw new Error(
                dados.mensagem || 'Erro ao excluir ponto.'
            );
        }

        alert(dados.mensagem);

        await carregarPontos();

    } catch (erro) {

        console.error('Erro ao excluir ponto:', erro);

        alert(erro.message);

        botao.disabled = false;

    }

}
