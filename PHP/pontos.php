
<?php

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');

mysqli_report(MYSQLI_REPORT_OFF);

/* ==========================================
   CONEXÃO COM O BANCO
========================================== */

try {

    require_once 'conexao.php';

} catch (Throwable $erro) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao conectar ao banco de dados."
    ]);

    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];


/* ==========================================
   LISTAR PONTOS
========================================== */

if ($metodo === 'GET') {

    $sql = "
        SELECT
            P.ID,
            P.Nome,
            B.Nome AS Bairro
        FROM Ponto P
        INNER JOIN Bairro B ON P.IDbairro = B.ID
        ORDER BY P.ID ASC
    ";

    $resultado = $conn->query($sql);

    if (!$resultado) {

        http_response_code(500);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao buscar pontos."
        ]);

        exit;
    }

    $pontos = [];

    while ($linha = $resultado->fetch_assoc()) {

        $pontos[] = $linha;

    }

    echo json_encode($pontos);

    exit;
}


/* ==========================================
   CADASTRAR PONTO
========================================== */

if ($metodo === 'POST') {

    $dados = json_decode(file_get_contents("php://input"), true);

    if (!is_array($dados)) {

        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Dados inválidos."
        ]);

        exit;
    }

    $nome = trim($dados['nome'] ?? '');

    $idBairro = intval($dados['id_bairro'] ?? 0);

    if ($nome === '') {

        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Informe o nome do ponto."
        ]);

        exit;
    }

    if ($idBairro <= 0) {

        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Selecione um bairro válido."
        ]);

        exit;
    }

    /* Verificar se o bairro existe */

    $sqlBairro = "SELECT ID FROM Bairro WHERE ID = ?";

    $stmtBairro = $conn->prepare($sqlBairro);

    if (!$stmtBairro) {

        http_response_code(500);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao verificar o bairro."
        ]);

        exit;
    }

    $stmtBairro->bind_param("i", $idBairro);

    $stmtBairro->execute();

    $resultadoBairro = $stmtBairro->get_result();

    if ($resultadoBairro->num_rows === 0) {

        $stmtBairro->close();

        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "O bairro selecionado não existe."
        ]);

        exit;
    }

    $stmtBairro->close();

    /* Inserir ponto */

    $sql = "
        INSERT INTO Ponto (IDbairro, Nome)
        VALUES (?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        http_response_code(500);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao preparar o cadastro do ponto."
        ]);

        exit;
    }

    $stmt->bind_param(
        "is",
        $idBairro,
        $nome
    );

    if ($stmt->execute()) {

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Ponto cadastrado com sucesso!"
        ]);

    } else {

        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível cadastrar o ponto."
        ]);

    }

    $stmt->close();

    exit;
}


/* ==========================================
   EXCLUIR PONTO
========================================== */

if ($metodo === 'DELETE') {

    $dados = json_decode(file_get_contents("php://input"), true);

    $id = intval($dados['id'] ?? 0);

    if ($id <= 0) {

        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "ID do ponto inválido."
        ]);

        exit;
    }

    $sql = "DELETE FROM Ponto WHERE ID = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        http_response_code(500);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao preparar a exclusão."
        ]);

        exit;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

        if ($stmt->affected_rows > 0) {

            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Ponto excluído com sucesso!"
            ]);

        } else {

            http_response_code(404);

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Ponto não encontrado."
            ]);

        }

    } else {

        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível excluir o ponto. Verifique se existem linhas vinculadas a ele."
        ]);

    }

    $stmt->close();

    exit;
}


/* ==========================================
   MÉTODO NÃO PERMITIDO
========================================== */

http_response_code(405);

echo json_encode([
    "sucesso" => false,
    "mensagem" => "Método não permitido."
]);

exit;

?>
