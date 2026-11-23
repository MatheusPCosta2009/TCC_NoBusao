
<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);

$login = trim($dados['login'] ?? '');
$senha = $dados['senha'] ?? '';

if (empty($login) || empty($senha)) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha todos os campos."
    ]);

    exit;
}

$sql = "SELECT ID FROM administrador WHERE login = ? AND senha = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao consultar o banco de dados."
    ]);

    exit;
}

$stmt->bind_param("ss", $login, $senha);

$stmt->execute();

$stmt->bind_result($id);

if ($stmt->fetch()) {

    session_regenerate_id(true);

    $_SESSION['admin_id'] = $id;

    echo json_encode([
        "sucesso" => true
    ]);

} else {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Login ou senha incorretos."
    ]);

}

$stmt->close();
$conn->close();

?>
