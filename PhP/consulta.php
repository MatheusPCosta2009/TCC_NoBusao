<?php

require_once "conexao.php";

header("Content-Type: application/json; charset=utf-8");


$acao = $_GET["acao"] ?? "";



if ($acao === "pontos") {

    $id_bairro = intval($_GET["id_bairro"] ?? 0);


    $sql = "
        SELECT
            id_ponto,
            nome

        FROM ponto

        WHERE id_bairro = ?

        ORDER BY nome
    ";


    $stmt = $conexao->prepare($sql);

    $stmt->bind_param(
        "i",
        $id_bairro
    );

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


if ($acao === "consultar") {

    $origem =
        intval($_GET["origem"] ?? 0);

    $destino =
        intval($_GET["destino"] ?? 0);


    if ($origem <= 0 || $destino <= 0) {

        echo json_encode([]);

        exit;
    }




    $sql = "

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

            SELECT pl1.id_linha

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

        HAVING ordem_origem < ordem_destino

        ORDER BY l.numero

    ";


    $stmt = $conexao->prepare($sql);


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


    $linhas = [];


    while ($linha = $resultado->fetch_assoc()) {


  

        $sqlHorario = "

            SELECT
                TIME_FORMAT(horario, '%H:%i') AS horario

            FROM horario

            WHERE id_linha = ?

            ORDER BY horario

        ";


        $stmtHorario =
            $conexao->prepare($sqlHorario);


        $stmtHorario->bind_param(
            "i",
            $linha["id_linha"]
        );


        $stmtHorario->execute();


        $resultadoHorario =
            $stmtHorario->get_result();


        $horarios = [];


        while (
            $horario =
            $resultadoHorario->fetch_assoc()
        ) {

            $horarios[] =
                $horario["horario"];

        }


        $linha["horarios"] =
            implode(
                " • ",
                $horarios
            );


        $linhas[] = $linha;

    }


    echo json_encode(
        $linhas,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}



echo json_encode([]);

?>
