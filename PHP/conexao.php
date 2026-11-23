
<?php

mysqli_report(MYSQLI_REPORT_OFF);

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "birigui";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    throw new Exception("Erro ao conectar ao banco de dados.");
}

$conn->set_charset("utf8mb4");

?>
