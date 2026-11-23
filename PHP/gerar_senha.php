<?php

require_once 'conexao.php';

$login = "admin";
$senha = "admin123";

$hash = password_hash($senha, PASSWORD_DEFAULT);

$sql = "UPDATE administrador SET senha = ? WHERE login = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hash, $login);

if ($stmt->execute()) {
    echo "Senha atualizada com sucesso!";
} else {
    echo "Erro ao atualizar senha.";
}

$stmt->close();
$conn->close();

?>