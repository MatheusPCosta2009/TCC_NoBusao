<?php
require_once __DIR__ . "/PHP/conexao.php";

// Captura e sanitização dos parâmetros GET
$bairro_origem  = $_GET["bairro_origem"] ?? "";
$ponto_origem   = $_GET["ponto_origem"] ?? "";
$bairro_destino = $_GET["bairro_destino"] ?? "";
$ponto_destino  = $_GET["ponto_destino"] ?? "";

// Busca todos os bairros para os selects
$bairros = [];
$sql = "SELECT id_bairro, nome FROM bairro ORDER BY nome";
$resultado = $conexao->query($sql);

if ($resultado) {
    while ($bairro = $resultado->fetch_assoc()) {
        $bairros[] = $bairro;
    }
}

/**
 * Função para buscar os pontos vinculados a um determinado bairro
 */
function buscarPontosPorBairro($conexao, $id_bairro) {
    if (empty($id_bairro) || !is_numeric($id_bairro)) {
        return [];
    }

    $pontos = [];
    $sql = "SELECT id_ponto, nome FROM ponto WHERE id_bairro = ? ORDER BY nome";
    $stmt = $conexao->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id_bairro);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($ponto = $resultado->fetch_assoc()) {
            $pontos[] = $ponto;
        }

        $stmt->close();
    }

    return $pontos;
}

// Carrega os pontos de origem e destino
$pontos_origem  = buscarPontosPorBairro($conexao, $bairro_origem);
$pontos_destino = buscarPontosPorBairro($conexao, $bairro_destino);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoBusão</title>
    <link rel="stylesheet" href="CSS/index.css">
</head>
<body>
    <div class="container">
        <header class="cabecalho">
            <div class="cabecalho-topo">
                <h1>No<span>Busão</span></h1>
                <a href="PHP/login.php" class="botao-adm">ADM</a>
            </div>
            <p class="descricao">Encontre a melhor rota de ônibus para o seu destino.</p>
        </header>

        <!-- Formulário de Seleção de Bairros e Pontos -->
        <form method="GET" action="index.php">
            <input type="hidden" name="ponto_origem" value="<?= htmlspecialchars($ponto_origem) ?>">
            <input type="hidden" name="ponto_destino" value="<?= htmlspecialchars($ponto_destino) ?>">

            <div class="rota">
                <!-- Bloco Origem -->
                <div class="bloco-rota">
                    <h2>Origem</h2>
                    
                    <div class="grupo">
                        <label for="bairro_origem">Bairro</label>
                        <select name="bairro_origem" id="bairro_origem" onchange="this.form.submit()">
                            <option value="">Selecione o bairro</option>
                            <?php foreach ($bairros as $bairro): ?>
                                <option value="<?= htmlspecialchars($bairro["id_bairro"]) ?>" <?= ($bairro_origem == $bairro["id_bairro"]) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($bairro["nome"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grupo">
                        <label for="ponto_origem">Ponto</label>
                        <select name="ponto_origem" id="ponto_origem" onchange="this.form.submit()" <?= empty($pontos_origem) ? "disabled" : "" ?>>
                            <option value="">Selecione o ponto</option>
                            <?php foreach ($pontos_origem as $ponto): ?>
                                <option value="<?= htmlspecialchars($ponto["id_ponto"]) ?>" <?= ($ponto_origem == $ponto["id_ponto"]) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($ponto["nome"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Bloco Destino -->
                <div class="bloco-rota">
                    <h2>Destino</h2>

                    <div class="grupo">
                        <label for="bairro_destino">Bairro</label>
                        <select name="bairro_destino" id="bairro_destino" onchange="this.form.submit()">
                            <option value="">Selecione o bairro</option>
                            <?php foreach ($bairros as $bairro): ?>
                                <option value="<?= htmlspecialchars($bairro["id_bairro"]) ?>" <?= ($bairro_destino == $bairro["id_bairro"]) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($bairro["nome"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grupo">
                        <label for="ponto_destino">Ponto</label>
                        <select name="ponto_destino" id="ponto_destino" onchange="this.form.submit()" <?= empty($pontos_destino) ? "disabled" : "" ?>>
                            <option value="">Selecione o ponto</option>
                            <?php foreach ($pontos_destino as $ponto): ?>
                                <option value="<?= htmlspecialchars($ponto["id_ponto"]) ?>" <?= ($ponto_destino == $ponto["id_ponto"]) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($ponto["nome"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </form>

        <!-- Formulário de Submissão para Busca de Linha -->
        <form method="GET" action="ROTA/achar_linha.php">
            <input type="hidden" name="ponto_origem" value="<?= htmlspecialchars($ponto_origem) ?>">
            <input type="hidden" name="ponto_destino" value="<?= htmlspecialchars($ponto_destino) ?>">
            <button type="submit" class="botao-rota" <?= ($ponto_origem === "" || $ponto_destino === "") ? "disabled" : "" ?>>
                Encontrar rota
            </button>
        </form>
    </div>
</body>
</html>