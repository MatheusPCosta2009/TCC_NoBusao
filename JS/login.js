
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('form-login');
    const mensagem = document.getElementById('mensagem');
    const botao = document.getElementById('botao-entrar');

    form.addEventListener('submit', async (e) => {

        e.preventDefault();

        const login = document.getElementById('login').value.trim();
        const senha = document.getElementById('senha').value;

        mensagem.textContent = '';
        botao.disabled = true;
        botao.textContent = 'Entrando...';

        try {

            const resposta = await fetch('../PHP/login.php', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    login: login,
                    senha: senha
                })

            });

            const resultado = await resposta.json();

            if (resultado.sucesso) {

                window.location.href = 'painel.html';

            } else {

                mensagem.textContent = resultado.mensagem;

            }

        } catch (erro) {

            console.error('Erro:', erro);

            mensagem.textContent = 'Não foi possível conectar ao servidor.';

        } finally {

            botao.disabled = false;
            botao.textContent = 'Entrar';

        }

    });

});
