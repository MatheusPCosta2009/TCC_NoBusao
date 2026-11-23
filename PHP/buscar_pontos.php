
<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/conexao.php";

$idBairro = filter_input(INPUT_GET, "id_bairro", FILTER_VALIDATE_INT);

if (!$idBairro) {
    http_response_code(400);

    echo json_encode([
        "erro" => "Bairro inválido."
    ]);

    exit;
}

$sql = "SELECT ID, Nome
        FROM ponto
        WHERE IDbairro = ?
        ORDER BY Nome ASC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);

    echo json_encode([
        "erro" => "Erro ao preparar consulta."
    ]);

    exit;
}

$stmt->bind_param("i", $idBairro);

$stmt->execute();

$resultado = $stmt->get_result();

$pontos = [];

while ($linha = $resultado->fetch_assoc()) {
    $pontos[] = [
        "ID" => (int) $linha["ID"],
        "Nome" => $linha["Nome"]
    ];
}

echo json_encode($pontos, JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();

?>
