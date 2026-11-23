
<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/conexao.php";

$sql = "SELECT ID, Nome FROM bairro ORDER BY Nome ASC";

$resultado = $conn->query($sql);

if (!$resultado) {
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro ao consultar os bairros."
    ]);
    exit;
}

$bairros = [];

while ($linha = $resultado->fetch_assoc()) {
    $bairros[] = [
        "ID" => (int) $linha["ID"],
        "Nome" => $linha["Nome"]
    ];
}

echo json_encode($bairros, JSON_UNESCAPED_UNICODE);

$conn->close();

?>
