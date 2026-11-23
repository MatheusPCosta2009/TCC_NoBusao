
<?php

header("Content-Type: application/json; charset=UTF-8");

mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . "/../conexao.php";


/* ==========================================
   RECEBER ORIGEM E DESTINO
========================================== */

$origem = filter_input(INPUT_GET, "origem", FILTER_VALIDATE_INT);
$destino = filter_input(INPUT_GET, "destino", FILTER_VALIDATE_INT);

if (!$origem || !$destino) {
    http_response_code(400);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Origem ou destino inválido."
    ]);

    exit;
}


/* ==========================================
   BUSCAR LINHA DIRETA
========================================== */

$sql = "
    SELECT
        l.ID AS IDlinha,
        l.Cor,
        pl1.ID AS IDpontolinha,
        pl1.Sentido,
        pl1.Ordemponto AS OrdemOrigem,
        pl2.Ordemponto AS OrdemDestino,
        p1.Nome AS NomeOrigem,
        p2.Nome AS NomeDestino

    FROM ponto_linha pl1

    INNER JOIN ponto_linha pl2
        ON pl1.IDlinha = pl2.IDlinha
        AND pl1.Sentido = pl2.Sentido
        AND pl2.Ordemponto > pl1.Ordemponto

    INNER JOIN linha l
        ON l.ID = pl1.IDlinha

    INNER JOIN ponto p1
        ON p1.ID = pl1.IDponto

    INNER JOIN ponto p2
        ON p2.ID = pl2.IDponto

    WHERE
        pl1.IDponto = ?
        AND pl2.IDponto = ?

    ORDER BY
        (pl2.Ordemponto - pl1.Ordemponto) ASC

    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao preparar a consulta da linha."
    ]);

    exit;
}

$stmt->bind_param("ii", $origem, $destino);
$stmt->execute();

$resultado = $stmt->get_result();

$linha = $resultado->fetch_assoc();

$stmt->close();


/* ==========================================
   VERIFICAR SE EXISTE LINHA
========================================== */

if (!$linha) {
    http_response_code(404);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não encontramos uma linha direta para esse trajeto."
    ]);

    exit;
}


/* ==========================================
   BUSCAR PONTOS DO PERCURSO
========================================== */

$sqlPontos = "
    SELECT
        p.ID,
        p.Nome,
        p.Latitude,
        p.Longitude

    FROM ponto_linha pl

    INNER JOIN ponto p
        ON p.ID = pl.IDponto

    WHERE
        pl.IDlinha = ?
        AND pl.Sentido = ?
        AND pl.Ordemponto BETWEEN ? AND ?

    ORDER BY pl.Ordemponto ASC
";

$stmtPontos = $conn->prepare($sqlPontos);

$stmtPontos->bind_param(
    "isii",
    $linha["IDlinha"],
    $linha["Sentido"],
    $linha["OrdemOrigem"],
    $linha["OrdemDestino"]
);

$stmtPontos->execute();

$resultadoPontos = $stmtPontos->get_result();

$pontos = [];

while ($ponto = $resultadoPontos->fetch_assoc()) {
    $pontos[] = $ponto;
}

$stmtPontos->close();


/* ==========================================
   BUSCAR HORÁRIOS NO PONTO DE ORIGEM
========================================== */

$sqlHorarios = "
    SELECT Hora

    FROM horario

    WHERE IDpontolinha = ?

    ORDER BY Hora ASC
";

$stmtHorarios = $conn->prepare($sqlHorarios);

$stmtHorarios->bind_param(
    "i",
    $linha["IDpontolinha"]
);

$stmtHorarios->execute();

$resultadoHorarios = $stmtHorarios->get_result();

$horarios = [];

while ($horario = $resultadoHorarios->fetch_assoc()) {
    $horarios[] = $horario["Hora"];
}

$stmtHorarios->close();


/* ==========================================
   RETORNAR RESULTADO
========================================== */

echo json_encode([
    "sucesso" => true,

    "linha" => [
        "id" => $linha["IDlinha"],
        "cor" => $linha["Cor"],
        "sentido" => $linha["Sentido"]
    ],

    "origem" => $linha["NomeOrigem"],
    "destino" => $linha["NomeDestino"],

    "horarios" => $horarios,

    "pontos" => $pontos

], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);


$conn->close();

?>
