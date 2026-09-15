<?php
require_once __DIR__ . "/../PHP/conexao.php";

// Captura dos parâmetros GET
$id_linha     = $_GET["id_linha"] ?? "";
$ponto_origem  = $_GET["ponto_origem"] ?? "";
$ponto_destino = $_GET["ponto_destino"] ?? "";
$sentido       = $_GET["sentido"] ?? "";

// Validação de entrada
if (!is_numeric($id_linha) || !is_numeric($ponto_origem) || !is_numeric($ponto_destino) || $sentido === "") {
    header("Location: ../index.php");
    exit;
}

$id_linha     = (int) $id_linha;
$ponto_origem  = (int) $ponto_origem;
$ponto_destino = (int) $ponto_destino;

// Consulta dados da linha
$sql = "SELECT id_linha, cor FROM linha WHERE id_linha = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_linha);
$stmt->execute();
$linha = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$linha) {
    header("Location: ../index.php");
    exit;
}

// Consulta nomes dos pontos de origem e destino
$sql = "SELECT id_ponto, nome FROM ponto WHERE id_ponto IN (?, ?)";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("ii", $ponto_origem, $ponto_destino);
$stmt->execute();
$resultado = $stmt->get_result();

$nome_origem  = "";
$nome_destino = "";

while ($ponto = $resultado->fetch_assoc()) {
    if ($ponto["id_ponto"] == $ponto_origem) {
        $nome_origem = $ponto["nome"];
    }
    if ($ponto["id_ponto"] == $ponto_destino) {
        $nome_destino = $ponto["nome"];
    }
}
$stmt->close();

// Consulta horários da linha no ponto de origem
$sql = "
    SELECT h.horario 
    FROM horario AS h
    INNER JOIN ponto_linha AS pl ON pl.id_ponto_linha = h.id_ponto_linha
    WHERE pl.id_linha = ? AND pl.id_ponto = ? AND pl.sentido = ?
    ORDER BY h.horario
";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("iis", $id_linha, $ponto_origem, $sentido);
$stmt->execute();
$resultado = $stmt->get_result();

$horarios = [];
while ($horario = $resultado->fetch_assoc()) {
    $horarios[] = date("H:i", strtotime($horario["horario"]));
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rota - NoBusão</title>
    <link rel="stylesheet" href="../CSS/index.css">
    <link rel="stylesheet" href="../CSS/resultado.css">
</head>
<body>
    <div class="container">
        <header class="cabecalho">
            <div class="cabecalho-topo">
                <h1>No<span>Busão</span></h1>
                <a href="../PHP/login.php" class="botao-adm">ADM</a>
            </div>
            <p class="descricao">Rota encontrada</p>
        </header>

        <main class="resultado">
            <div class="linha-resultado">
                <h2>Linha <?= htmlspecialchars($linha["id_linha"]) ?></h2>
                <p class="cor"><?= htmlspecialchars($linha["cor"]) ?></p>

                <div class="ponto">
                    <strong>Embarque</strong>
                    <span><?= htmlspecialchars($nome_origem) ?></span>
                </div>

                <div class="horarios-box">
                    <strong>Horários de embarque</strong>
                    <?php if (!empty($horarios)): ?>
                        <div class="horarios">
                            <?php foreach ($horarios as $horario): ?>
                                <span class="horario"><?= htmlspecialchars($horario) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Nenhum horário cadastrado.</p>
                    <?php endif; ?>
                </div>

                <div class="ponto">
                    <strong>Desembarque</strong>
                    <span><?= htmlspecialchars($nome_destino) ?></span>
                </div>
            </div>

            <a href="../index.php" class="botao-voltar">← Voltar</a>
        </main>
    </div>
</body>
</html>