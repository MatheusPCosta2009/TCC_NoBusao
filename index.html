<?php

require_once "php/conexao.php";

$bairro_origem = $conexao->query("
    SELECT id_bairro, nome
    FROM bairro
    ORDER BY nome
");

$bairro_destino = $conexao->query("
    SELECT id_bairro, nome
    FROM bairro
    ORDER BY nome
");

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="CSS/index.css">

    <title>NoBusão - Consulta</title>
    

</head>

<body>

<div class="container">

    <!-- CABEÇALHO -->

    <div class="cabecalho">

        <h1>
            No<span>Busão</span>
        </h1>

        <p class="descricao">
            Encontre a melhor linha de ônibus para o seu trajeto.
        </p>

    </div>


    <!-- ORIGEM E DESTINO -->

    <div class="rota">


        <!-- ORIGEM -->

        <div class="bloco-rota">

            <h2>
                Origem
            </h2>

            <div class="grupo">

                <label for="bairro_origem">
                    Bairro
                </label>

                <select id="bairro_origem">

                    <option value="">
                        Selecione o bairro
                    </option>

                    <?php while ($bairro = $bairro_origem->fetch_assoc()): ?>

                        <option value="<?= $bairro['id_bairro'] ?>">
                            <?= htmlspecialchars($bairro['nome']) ?>
                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <div class="grupo">

                <label for="ponto_origem">
                    Ponto
                </label>

                <select id="ponto_origem">

                    <option value="">
                        Primeiro selecione o bairro
                    </option>

                </select>

            </div>

        </div>


        <!-- DESTINO -->

        <div class="bloco-rota">

            <h2>
                Destino
            </h2>

            <div class="grupo">

                <label for="bairro_destino">
                    Bairro
                </label>

                <select id="bairro_destino">

                    <option value="">
                        Selecione o bairro
                    </option>

                    <?php while ($bairro = $bairro_destino->fetch_assoc()): ?>

                        <option value="<?= $bairro['id_bairro'] ?>">
                            <?= htmlspecialchars($bairro['nome']) ?>
                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <div class="grupo">

                <label for="ponto_destino">
                    Ponto
                </label>

                <select id="ponto_destino">

                    <option value="">
                        Primeiro selecione o bairro
                    </option>

                </select>

            </div>

        </div>

    </div>


    <!-- BOTÃO -->

    <button type="button" onclick="consultar()">
        Consultar ônibus
    </button>


    <!-- RESULTADO -->

    <div id="resultado"></div>

</div>


<script>

/*
=========================================================
CARREGAR PONTOS DO BAIRRO
=========================================================
*/

function carregarPontos(idBairro, idSelect) {

    const select = document.getElementById(idSelect);

    select.innerHTML =
        '<option value="">Carregando...</option>';


    if (idBairro === "") {

        select.innerHTML =
            '<option value="">Primeiro selecione o bairro</option>';

        return;
    }


    fetch(
        "php/consulta.php?acao=pontos&id_bairro=" +
        encodeURIComponent(idBairro)
    )

    .then(response => {

        if (!response.ok) {
            throw new Error("Erro na requisição");
        }

        return response.json();

    })

    .then(dados => {

        select.innerHTML =
            '<option value="">Selecione o ponto</option>';


        if (!Array.isArray(dados) || dados.length === 0) {

            select.innerHTML =
                '<option value="">Nenhum ponto encontrado</option>';

            return;
        }


        dados.forEach(ponto => {

            const option =
                document.createElement("option");

            option.value =
                ponto.id_ponto;

            option.textContent =
                ponto.nome;

            select.appendChild(option);

        });

    })

    .catch(() => {

        select.innerHTML =
            '<option value="">Erro ao carregar pontos</option>';

    });

}


/*
=========================================================
BAIRRO DE ORIGEM
=========================================================
*/

document
    .getElementById("bairro_origem")
    .addEventListener("change", function() {

        carregarPontos(
            this.value,
            "ponto_origem"
        );

    });


/*
=========================================================
BAIRRO DE DESTINO
=========================================================
*/

document
    .getElementById("bairro_destino")
    .addEventListener("change", function() {

        carregarPontos(
            this.value,
            "ponto_destino"
        );

    });


/*
=========================================================
CONSULTAR LINHAS
=========================================================
*/

function consultar() {

    const origem =
        document.getElementById("ponto_origem").value;

    const destino =
        document.getElementById("ponto_destino").value;

    const resultado =
        document.getElementById("resultado");


    /*
    -----------------------------------------------------
    VALIDAR CAMPOS
    -----------------------------------------------------
    */

    if (origem === "" || destino === "") {

        resultado.innerHTML =
            "<p>Selecione o ponto de origem e o ponto de destino.</p>";

        return;
    }


    /*
    -----------------------------------------------------
    MESMO PONTO
    -----------------------------------------------------
    */

    if (origem === destino) {

        resultado.innerHTML =
            "<p>O ponto de origem e o ponto de destino são iguais.</p>";

        return;
    }


    /*
    -----------------------------------------------------
    CARREGANDO
    -----------------------------------------------------
    */

    resultado.innerHTML =
        "<p>Consultando linhas de ônibus...</p>";


    /*
    -----------------------------------------------------
    CONSULTA
    -----------------------------------------------------
    */

    fetch(
        "php/consulta.php?acao=consultar" +
        "&origem=" +
        encodeURIComponent(origem) +
        "&destino=" +
        encodeURIComponent(destino)
    )

    .then(response => {

        if (!response.ok) {
            throw new Error("Erro na requisição");
        }

        return response.json();

    })

    .then(dados => {

        /*
        -------------------------------------------------
        NENHUMA LINHA
        -------------------------------------------------
        */

        if (!Array.isArray(dados) || dados.length === 0) {

            resultado.innerHTML =
                "<p>Nenhuma linha encontrada para esse trajeto.</p>";

            return;
        }


        /*
        -------------------------------------------------
        MONTAR RESULTADOS
        -------------------------------------------------
        */

        let html = "";


        dados.forEach(linha => {

            html += `

                <div class="card-linha">

                    <h2>
                        Linha ${linha.numero}
                    </h2>

                    <p>
                        ${linha.nome}
                    </p>

                    <p class="titulo-horarios">
                        Horários
                    </p>

                    <p>
                        ${linha.horarios ?? "Horários não informados."}
                    </p>

                </div>

            `;

        });


        resultado.innerHTML = html;

    })

    .catch(erro => {

        console.error(erro);

        resultado.innerHTML =
            "<p>Erro ao realizar a consulta.</p>";

    });

}

</script>

</body>

</html>
