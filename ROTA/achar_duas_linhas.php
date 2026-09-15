<?php
require_once __DIR__ . "/../PHP/conexao.php";

// Captura dos parâmetros GET
$ponto_origem  = $_GET["ponto_origem"] ?? "";
$ponto_destino = $_GET["ponto_destino"] ?? "";

// Validação de entrada
if (
    $ponto_origem === "" || 
    $ponto_destino === "" || 
    !is_numeric($ponto_origem) || 
    !is_numeric($ponto_destino)
) {
    header("Location: ../index.php");
    exit;
}

// Conversão de tipos
$ponto_origem  = (int) $ponto_origem;
$ponto_destino = (int) $ponto_destino;

// Consulta por rota com integração em duas linhas de ônibus
$sql = "
    SELECT 
        l1.id_linha AS linha1, 
        l1.cor AS cor1, 
        pl_origem.sentido AS sentido1,
        l2.id_linha AS linha2, 
        l2.cor AS cor2, 
        pl_transferencia2.sentido AS sentido2,
        pl_origem.id_ponto AS ponto_origem,
        pl_transferencia1.id_ponto AS ponto_transferencia,
        pl_destino.id_ponto AS ponto_destino
    FROM ponto_linha AS pl_origem
    INNER JOIN linha AS l1 
        ON l1.id_linha = pl_origem.id_linha
    INNER JOIN ponto_linha AS pl_transferencia1
        ON pl_transferencia1.id_linha = pl_origem.id_linha
        AND pl_transferencia1.sentido = pl_origem.sentido
        AND pl_transferencia1.ordem_ponto > pl_origem.ordem_ponto
    INNER JOIN ponto_linha AS pl_transferencia2
        ON pl_transferencia2.id_ponto = pl_transferencia1.id_ponto
        AND pl_transferencia2.id_linha <> pl_origem.id_linha
    INNER JOIN linha AS l2 
        ON l2.id_linha = pl_transferencia2.id_linha
    INNER JOIN ponto_linha AS pl_destino
        ON pl_destino.id_linha = pl_transferencia2.id_linha
        AND pl_destino.sentido = pl_transferencia2.sentido
        AND pl_destino.id_ponto = ?
        AND pl_destino.ordem_ponto > pl_transferencia2.ordem_ponto
    WHERE pl_origem.id_ponto = ?
    LIMIT 1
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("ii", $ponto_destino, $ponto_origem);
$stmt->execute();
$rota = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Caso encontre uma rota com integração
if ($rota) {
    $query = http_build_query([
        "linha1"               => $rota["linha1"],
        "cor1"                 => $rota["cor1"],
        "sentido1"             => $rota["sentido1"],
        "linha2"               => $rota["linha2"],
        "cor2"                 => $rota["cor2"],
        "sentido2"             => $rota["sentido2"],
        "ponto_origem"         => $rota["ponto_origem"],
        "ponto_transferencia"  => $rota["ponto_transferencia"],
        "ponto_destino"        => $rota["ponto_destino"]
    ]);

    header("Location: resultado_duas_linhas.php?" . $query);
    exit;
}

// Se nenhuma rota com duas linhas for encontrada
header("Location: ../index.php");
exit;