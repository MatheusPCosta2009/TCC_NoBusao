<?php
require_once __DIR__ . "/../PHP/conexao.php";

// Captura dos parâmetros GET
$linha1               = $_GET["linha1"] ?? "";
$cor1                 = $_GET["cor1"] ?? "";
$sentido1             = $_GET["sentido1"] ?? "";
$linha2               = $_GET["linha2"] ?? "";
$cor2                 = $_GET["cor2"] ?? "";
$sentido2             = $_GET["sentido2"] ?? "";
$ponto_origem         = $_GET["ponto_origem"] ?? "";
$ponto_transferencia  = $_GET["ponto_transferencia"] ?? "";
$ponto_destino        = $_GET["ponto_destino"] ?? "";

// Validação de entrada
if (
    !is_numeric($linha1) || 
    !is_numeric($linha2) || 
    !is_numeric($ponto_origem) || 
    !is_numeric($ponto_transferencia) || 
    !is_numeric($ponto_destino) || 
    $sentido1 === "" || 
    $sentido2 === ""
) {
    header("Location: ../index.php");
    exit;
}

// Conversão de tipos
$linha1              = (int) $linha1;
$linha2              = (int) $linha2;
$ponto_origem        = (int) $ponto_origem;
$ponto_transferencia = (int) $ponto_transferencia;
$ponto_destino       = (int) $ponto_destino;

// Busca os nomes dos pontos (origem, transferência e destino)
$sql = "SELECT id_ponto, nome FROM ponto WHERE id_ponto IN (?, ?, ?)";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("iii", $ponto_origem, $ponto_transferencia, $ponto_destino);
$stmt->execute();
$resultado = $stmt->get_result();

$nome_origem        = "";
$nome_transferencia = "";
$nome_destino       = "";

while ($ponto = $resultado->fetch_assoc()) {
    if ($ponto["id_ponto"] == $ponto_origem) {
        $nome_origem = $ponto["nome"];
    }
    if ($ponto["id_ponto"] == $ponto_transferencia) {
        $nome_transferencia = $ponto["nome"];
    }
    if ($ponto["id_ponto"] == $ponto_destino) {
        $nome_destino = $ponto["nome"];
    }
}
$stmt->close();

/**
 * Função para buscar os horários de uma linha em determinado ponto e sentido
 */
function buscarHorarios($conexao, $linha, $ponto, $sentido)
{
    $sql = "
        SELECT h.horario 
        FROM horario AS h 
        INNER JOIN ponto_linha AS pl ON pl.id_ponto_linha = h.id_ponto_linha 
        WHERE pl.id_linha = ? AND pl.id_ponto = ? AND pl.sentido = ? 
        ORDER BY h.horario
    ";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("iis", $linha, $ponto, $sentido);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $horarios = [];
    while ($row = $resultado->fetch_assoc()) {
        $horarios[] = date("H:i", strtotime($row["horario"]));
    }
    $stmt->close();

    return $horarios;
}

// Busca os horários das duas linhas
$horarios1 = buscarHorarios($conexao, $linha1, $ponto_origem, $sentido1);
$horarios2 = buscarHorarios($conexao, $linha2, $ponto_transferencia, $sentido2);
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
            <p class="descricao">Rota encontrada com duas linhas</p>
        </header>

        <main class="resultado">
            <!-- Primeira Linha -->
            <div class="linha-resultado">
                <h2>Linha <?= htmlspecialchars($linha1) ?></h2>
                <p class="cor"><?= htmlspecialchars($cor1) ?></p>

                <div class="ponto">
                    <strong>Embarque</strong>
                    <span><?= htmlspecialchars($nome_origem) ?></span>
                </div>

                <div class="horarios-box">
                    <strong>Horários da linha</strong>
                    <?php if (!empty($horarios1)): ?>
                        <div class="horarios">
                            <?php foreach ($horarios1 as $h): ?>
                                <span class="horario"><?= htmlspecialchars($h) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Nenhum horário cadastrado.</p>
                    <?php endif; ?>
                </div>

                <div class="ponto">
                    <strong>Troca de ônibus</strong>
                    <span><?= htmlspecialchars($nome_transferencia) ?></span>
                </div>
            </div>

            <!-- Segunda Linha -->
            <div class="linha-resultado" style="margin-top:20px;">
                <h2>Linha <?= htmlspecialchars($linha2) ?></h2>
                <p class="cor"><?= htmlspecialchars($cor2) ?></p>

                <div class="ponto">
                    <strong>Embarque na segunda linha</strong>
                    <span><?= htmlspecialchars($nome_transferencia) ?></span>
                </div>

                <div class="horarios-box">
                    <strong>Horários da linha</strong>
                    <?php if (!empty($horarios2)): ?>
                        <div class="horarios">
                            <?php foreach ($horarios2 as $h): ?>
                                <span class="horario"><?= htmlspecialchars($h) ?></span>
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