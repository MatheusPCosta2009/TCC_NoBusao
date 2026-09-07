<?php

$conexao = new mysqli(
    "localhost",
    "root",
    "",
    "onibus"
);

if ($conexao->connect_error) {
    die("Erro ao conectar com o banco de dados: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

?>
