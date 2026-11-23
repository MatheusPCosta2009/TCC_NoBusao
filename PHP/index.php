
<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/PHP/conexao.php";

// Buscar bairros
$bairros = [];

$sqlBairros = "SELECT ID, Nome FROM bairro ORDER BY Nome ASC";

$resultadoBairros = $conn->query($sqlBairros);

if (!$resultadoBairros) {
    http_response_code(500);
    echo json_encode(["erro" => "Erro ao buscar bairros."]);
    exit;
}

while ($bairro = $resultadoBairros->fetch_assoc()) {
    $bairros[] = $bairro;
}


// Buscar pontos
$pontos = [];

$sqlPontos = "SELECT ID, IDbairro, Nome FROM ponto ORDER BY Nome ASC";

$resultadoPontos = $conn->query($sqlPontos);

if (!$resultadoPontos) {
    http_response_code(500);
    echo json_encode(["erro" => "Erro ao buscar pontos."]);
    exit;
}

while ($ponto = $resultadoPontos->fetch_assoc()) {
    $pontos[] = $ponto;
}


// Enviar os dados
echo json_encode([
    "bairros" => $bairros,
    "pontos" => $pontos
], JSON_UNESCAPED_UNICODE);

$conn->close();

?>
