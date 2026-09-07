<?php

require_once "conexao.php";

header("Content-Type: application/json; charset=utf-8");

$acao = $_GET["acao"] ?? "";


/*
=========================================================
CARREGAR PONTOS DE UM BAIRRO
=========================================================
*/

if ($acao === "pontos") {

    $id_bairro = intval($_GET["id_bairro"] ?? 0);

    if ($id_bairro <= 0) {
        echo json_encode([]);
        exit;
    }

    $sql = "
        SELECT
            id_ponto,
            nome
        FROM ponto
        WHERE id_bairro = ?
        ORDER BY nome
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_bairro);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $pontos = [];

    while ($ponto = $resultado->fetch_assoc()) {
        $pontos[] = $ponto;
    }

    echo json_encode(
        $pontos,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*
=========================================================
BUSCAR HORÁRIOS DE UMA LINHA
=========================================================
*/

function buscarHorarios($conexao, $id_linha)
{
    $sql = "
        SELECT
            TIME_FORMAT(horario, '%H:%i') AS horario
        FROM horario
        WHERE id_linha = ?
        ORDER BY horario
    ";

    $stmt = $conexao->prepare($sql);

    $stmt->bind_param(
        "i",
        $id_linha
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    $horarios = [];

    while ($horario = $resultado->fetch_assoc()) {
        $horarios[] = $horario["horario"];
    }

    return implode(
        " • ",
        $horarios
    );
}


/*
=========================================================
CONSULTAR
=========================================================
*/

if ($acao === "consultar") {

    $origem =
        intval($_GET["origem"] ?? 0);

    $destino =
        intval($_GET["destino"] ?? 0);


    if (
        $origem <= 0 ||
        $destino <= 0
    ) {

        echo json_encode([]);

        exit;
    }


    /*
    =====================================================
    1. PROCURAR LINHA DIRETA
    =====================================================
    */

    $sqlDireta = "

        SELECT

            l.id_linha,
            l.numero,
            l.nome,

            MIN(
                CASE
                    WHEN pl.id_ponto = ?
                    THEN pl.ordem
                END
            ) AS ordem_origem,

            MIN(
                CASE
                    WHEN pl.id_ponto = ?
                    THEN pl.ordem
                END
            ) AS ordem_destino

        FROM linha l

        INNER JOIN ponto_linha pl
            ON pl.id_linha = l.id_linha

        WHERE l.id_linha IN (

            SELECT
                pl1.id_linha

            FROM ponto_linha pl1

            INNER JOIN ponto_linha pl2
                ON pl1.id_linha = pl2.id_linha

            WHERE pl1.id_ponto = ?
              AND pl2.id_ponto = ?

        )

        GROUP BY
            l.id_linha,
            l.numero,
            l.nome

        HAVING
            ordem_origem < ordem_destino

        ORDER BY
            l.numero

    ";


    $stmt =
        $conexao->prepare($sqlDireta);


    $stmt->bind_param(
        "iiii",
        $origem,
        $destino,
        $origem,
        $destino
    );


    $stmt->execute();


    $resultado =
        $stmt->get_result();


    $linhasDiretas = [];


    while (
        $linha =
        $resultado->fetch_assoc()
    ) {

        $linha["tipo"] = "direta";

        $linha["horarios"] =
            buscarHorarios(
                $conexao,
                $linha["id_linha"]
            );

        $linhasDiretas[] =
            $linha;
    }


    /*
    =====================================================
    SE EXISTIR LINHA DIRETA
    MOSTRA AS LINHAS DIRETAS
    =====================================================
    */

    if (
        count($linhasDiretas) > 0
    ) {

        echo json_encode(
            $linhasDiretas,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    /*
    =====================================================
    2. LINHAS DA ORIGEM
    =====================================================
    */

    $sqlOrigem = "

        SELECT

            l.id_linha,
            l.numero,
            l.nome,
            pl.ordem

        FROM linha l

        INNER JOIN ponto_linha pl
            ON pl.id_linha = l.id_linha

        WHERE pl.id_ponto = ?

        ORDER BY
            l.numero

    ";


    $stmtOrigem =
        $conexao->prepare($sqlOrigem);


    $stmtOrigem->bind_param(
        "i",
        $origem
    );


    $stmtOrigem->execute();


    $resultadoOrigem =
        $stmtOrigem->get_result();


    $linhasOrigem = [];


    while (
        $linha =
        $resultadoOrigem->fetch_assoc()
    ) {

        $linhasOrigem[] =
            $linha;
    }


    /*
    =====================================================
    3. LINHAS DO DESTINO
    =====================================================
    */

    $sqlDestino = "

        SELECT

            l.id_linha,
            l.numero,
            l.nome,
            pl.ordem

        FROM linha l

        INNER JOIN ponto_linha pl
            ON pl.id_linha = l.id_linha

        WHERE pl.id_ponto = ?

        ORDER BY
            l.numero

    ";


    $stmtDestino =
        $conexao->prepare($sqlDestino);


    $stmtDestino->bind_param(
        "i",
        $destino
    );


    $stmtDestino->execute();


    $resultadoDestino =
        $stmtDestino->get_result();


    $linhasDestino = [];


    while (
        $linha =
        $resultadoDestino->fetch_assoc()
    ) {

        $linhasDestino[] =
            $linha;
    }


    /*
    =====================================================
    4. PROCURAR UMA ÚNICA ROTA COM TRANSFERÊNCIA
    =====================================================
    */

    foreach (
        $linhasOrigem as $linha1
    ) {

        foreach (
            $linhasDestino as $linha2
        ) {


            /*
            ---------------------------------------------
            NÃO USAR A MESMA LINHA
            ---------------------------------------------
            */

            if (
                $linha1["id_linha"] ==
                $linha2["id_linha"]
            ) {

                continue;
            }


            /*
            ---------------------------------------------
            PONTOS DA PRIMEIRA LINHA
            DEPOIS DA ORIGEM
            ---------------------------------------------
            */

            $sqlPontosLinha1 = "

                SELECT

                    pl.id_ponto,
                    pl.ordem,
                    p.nome

                FROM ponto_linha pl

                INNER JOIN ponto p
                    ON p.id_ponto = pl.id_ponto

                WHERE pl.id_linha = ?
                  AND pl.ordem >= ?

                ORDER BY
                    pl.ordem ASC

            ";


            $stmtPontos1 =
                $conexao->prepare(
                    $sqlPontosLinha1
                );


            $stmtPontos1->bind_param(
                "ii",
                $linha1["id_linha"],
                $linha1["ordem"]
            );


            $stmtPontos1->execute();


            $resultadoPontos1 =
                $stmtPontos1->get_result();


            $pontosLinha1 = [];


            while (
                $ponto =
                $resultadoPontos1->fetch_assoc()
            ) {

                $pontosLinha1[
                    $ponto["id_ponto"]
                ] = $ponto;
            }


            /*
            ---------------------------------------------
            PONTOS DA SEGUNDA LINHA
            ANTES DO DESTINO
            ---------------------------------------------
            */

            $sqlPontosLinha2 = "

                SELECT

                    pl.id_ponto,
                    pl.ordem,
                    p.nome

                FROM ponto_linha pl

                INNER JOIN ponto p
                    ON p.id_ponto = pl.id_ponto

                WHERE pl.id_linha = ?
                  AND pl.ordem <= ?

                ORDER BY
                    pl.ordem DESC

            ";


            $stmtPontos2 =
                $conexao->prepare(
                    $sqlPontosLinha2
                );


            $stmtPontos2->bind_param(
                "ii",
                $linha2["id_linha"],
                $linha2["ordem"]
            );


            $stmtPontos2->execute();


            $resultadoPontos2 =
                $stmtPontos2->get_result();


            /*
            ---------------------------------------------
            ENCONTRAR PONTO DE INTEGRAÇÃO
            ---------------------------------------------
            */

            $integracao = null;


            while (
                $ponto2 =
                $resultadoPontos2->fetch_assoc()
            ) {

                $idPonto =
                    $ponto2["id_ponto"];


                if (
                    isset(
                        $pontosLinha1[$idPonto]
                    )
                ) {

                    $integracao =
                        $pontosLinha1[$idPonto];

                    break;
                }
            }


            /*
            ---------------------------------------------
            SE ENCONTROU UMA ROTA
            ---------------------------------------------
            */

            if (
                $integracao !== null
            ) {

                /*
                =========================================
                RETORNAMOS IMEDIATAMENTE
                APENAS ESTA ROTA
                =========================================
                */

                $rota = [

                    "tipo" =>
                        "transferencia",


                    "linha1" => [

                        "id_linha" =>
                            $linha1["id_linha"],

                        "numero" =>
                            $linha1["numero"],

                        "nome" =>
                            $linha1["nome"],

                        "horarios" =>
                            buscarHorarios(
                                $conexao,
                                $linha1["id_linha"]
                            )

                    ],


                    "integracao" => [

                        "id_ponto" =>
                            $integracao["id_ponto"],

                        "nome" =>
                            $integracao["nome"]

                    ],


                    "linha2" => [

                        "id_linha" =>
                            $linha2["id_linha"],

                        "numero" =>
                            $linha2["numero"],

                        "nome" =>
                            $linha2["nome"],

                        "horarios" =>
                            buscarHorarios(
                                $conexao,
                                $linha2["id_linha"]
                            )

                    ]

                ];


                echo json_encode(
                    [$rota],
                    JSON_UNESCAPED_UNICODE
                );

                exit;
            }

        }

    }


    /*
    =====================================================
    NENHUMA ROTA ENCONTRADA
    =====================================================
    */

    echo json_encode([]);

    exit;
}


/*
=========================================================
AÇÃO INVÁLIDA
=========================================================
*/

echo json_encode([]);

?>
