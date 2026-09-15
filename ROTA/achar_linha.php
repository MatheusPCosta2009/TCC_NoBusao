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

// Busca por uma única linha direta ligando a origem ao destino
$sql = "
    SELECT 
        l.id_linha, 
        l.cor, 
        pl_origem.sentido,
        pl_origem.id_ponto AS ponto_origem,
        pl_destino.id_ponto AS ponto_destino
    FROM ponto_linha AS pl_origem
    INNER JOIN ponto_linha AS pl_destino
        ON pl_destino.id_linha = pl_origem.id_linha
        AND pl_destino.sentido = pl_origem.sentido
        AND pl_destino.ordem_ponto > pl_origem.ordem_ponto
    INNER JOIN linha AS l 
        ON l.id_linha = pl_origem.id_linha
    WHERE pl_origem.id_ponto = ? AND pl_destino.id_ponto = ?
    LIMIT 1
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("ii", $ponto_origem, $ponto_destino);
$stmt->execute();
$linha = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Caso encontre rota direta com 1 linha
if ($linha) {
    $query = http_build_query([
        "id_linha"      => $linha["id_linha"],
        "cor"           => $linha["cor"],
        "ponto_origem"  => $linha["ponto_origem"],
        "ponto_destino" => $linha["ponto_destino"],
        "sentido"       => $linha["sentido"]
    ]);

    header("Location: resultado_linha.php?" . $query);
    exit;
}

// Caso precise buscar transferência com 2 linhas
$query = http_build_query([
    "ponto_origem"  => $ponto_origem,
    "ponto_destino" => $ponto_destino
]);

header("Location: achar_duas_linhas.php?" . $query);
exit;