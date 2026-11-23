
<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "conexao.php";

$metodo = $_SERVER["REQUEST_METHOD"];

// LISTAR BAIRROS

if ($metodo === "GET") {

    $sql = "SELECT ID, Nome FROM bairro ORDER BY ID ASC";

    $resultado = $conn->query($sql);

    if (!$resultado) {

        http_response_code(500);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao buscar bairros."
        ]);

        exit;
    }

    $bairros = [];

    while ($linha = $resultado->fetch_assoc()) {

        $bairros[] = [
            "id" => $linha["ID"],
            "nome" => $linha["Nome"]
        ];

    }

    echo json_encode($bairros, JSON_UNESCAPED_UNICODE);

    exit;
}

// CADASTRAR BAIRRO

if ($metodo === "POST") {

    $dados = json_decode(file_get_contents("php://input"), true);

    $nome = trim($dados["nome"] ?? "");

    if ($nome === "") {

        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Informe o nome do bairro."
        ]);

        exit;
    }

    // VERIFICAR DUPLICIDADE

    $sql = "SELECT ID FROM bairro WHERE Nome = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao preparar consulta."
        ]);
        exit;
    }

    $stmt->bind_param("s", $nome);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        http_response_code(409);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este bairro já está cadastrado."
        ]);

        exit;
    }

    $stmt->close();

    // INSERIR BAIRRO

    $sql = "INSERT INTO bairro (Nome) VALUES (?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome);

    if ($stmt->execute()) {

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Bairro cadastrado com sucesso."
        ]);

    } else {

        http_response_code(500);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao cadastrar o bairro."
        ]);

    }

    $stmt->close();

    exit;
}

// EXCLUIR BAIRRO

if ($metodo === "DELETE") {

    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {

        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "ID do bairro inválido."
        ]);

        exit;
    }

    $sql = "DELETE FROM bairro WHERE ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

        if ($stmt->affected_rows > 0) {

            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Bairro excluído com sucesso."
            ]);

        } else {

            http_response_code(404);

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Bairro não encontrado."
            ]);

        }

    } else {

        if ($conn->errno == 1451) {

            http_response_code(409);

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não é possível excluir este bairro porque existem pontos vinculados a ele."
            ]);

        } else {

            http_response_code(500);

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Erro ao excluir o bairro."
            ]);

        }

    }

    $stmt->close();

    exit;
}

// MÉTODO NÃO PERMITIDO

http_response_code(405);

echo json_encode([
    "sucesso" => false,
    "mensagem" => "Método não permitido."
]);

?>
