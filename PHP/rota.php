
<?php

require_once __DIR__ . "/conexao.php";

// ==========================================
// RECEBER DADOS DO FORMULÁRIO
// ==========================================

$bairroOrigem = filter_input(INPUT_GET, "bairro_origem", FILTER_VALIDATE_INT);
$pontoOrigem = filter_input(INPUT_GET, "ponto_origem", FILTER_VALIDATE_INT);

$bairroDestino = filter_input(INPUT_GET, "bairro_destino", FILTER_VALIDATE_INT);
$pontoDestino = filter_input(INPUT_GET, "ponto_destino", FILTER_VALIDATE_INT);


// ==========================================
// FUNÇÕES AUXILIARES
// ==========================================

function erro($mensagem) {
    echo "<h2>" . htmlspecialchars($mensagem) . "</h2>";
    exit;
}

function buscarNomePonto($conn, $id) {

    $sql = "SELECT Nome FROM ponto WHERE ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $dados = $resultado->fetch_assoc();

    return $dados["Nome"] ?? null;
}

function enviarResultado($dados) {

    $url = "../RESULTADO/resultado.html?" .
        http_build_query($dados, "", "&", PHP_QUERY_RFC3986);

    header("Location: " . $url);
    exit;
}


// ==========================================
// VALIDAR DADOS RECEBIDOS
// ==========================================

if (
    !$bairroOrigem ||
    !$pontoOrigem ||
    !$bairroDestino ||
    !$pontoDestino
) {
    erro("Selecione corretamente a origem e o destino.");
}

if ($pontoOrigem === $pontoDestino) {
    erro("A origem e o destino não podem ser iguais.");
}


// ==========================================
// VERIFICAR SE OS PONTOS PERTENCEM AOS BAIRROS
// ==========================================

$sqlValidar = "
    SELECT ID
    FROM ponto
    WHERE (ID = ? AND IDbairro = ?)
       OR (ID = ? AND IDbairro = ?)
";

$stmt = $conn->prepare($sqlValidar);

$stmt->bind_param(
    "iiii",
    $pontoOrigem,
    $bairroOrigem,
    $pontoDestino,
    $bairroDestino
);

$stmt->execute();

$resultadoValidacao = $stmt->get_result();

if ($resultadoValidacao->num_rows !== 2) {
    erro("Um dos pontos não pertence ao bairro selecionado.");
}


// ==========================================
// BUSCAR NOME DA ORIGEM E DO DESTINO
// ==========================================

$nomeOrigem = buscarNomePonto($conn, $pontoOrigem);
$nomeDestino = buscarNomePonto($conn, $pontoDestino);

if (!$nomeOrigem || !$nomeDestino) {
    erro("Não foi possível localizar os pontos selecionados.");
}


// ==========================================
// BUSCAR TODAS AS POSIÇÕES DAS LINHAS
// ==========================================

$sql = "
    SELECT
        pl.IDlinha,
        pl.IDponto,
        pl.Ordemponto,
        pl.Sentido,
        l.Cor

    FROM ponto_linha pl

    INNER JOIN linha l
        ON l.ID = pl.IDlinha

    ORDER BY
        pl.IDlinha,
        pl.Sentido,
        pl.Ordemponto
";

$resultado = $conn->query($sql);

if (!$resultado) {
    erro("Erro ao consultar as linhas.");
}

$registros = [];

while ($registro = $resultado->fetch_assoc()) {
    $registros[] = $registro;
}


// ==========================================
// IDENTIFICAR REGISTROS DE ORIGEM E DESTINO
// ==========================================

$rotasOrigem = [];
$rotasDestino = [];

foreach ($registros as $registro) {

    if ((int)$registro["IDponto"] === $pontoOrigem) {
        $rotasOrigem[] = $registro;
    }

    if ((int)$registro["IDponto"] === $pontoDestino) {
        $rotasDestino[] = $registro;
    }
}

if (empty($rotasOrigem) || empty($rotasDestino)) {
    erro("Não foram encontradas linhas para um dos pontos.");
}


// ==========================================
// PROCURAR ROTA DIRETA (UMA LINHA)
// ==========================================

foreach ($rotasOrigem as $origem) {

    foreach ($rotasDestino as $destino) {

        $mesmaLinha =
            $origem["IDlinha"] == $destino["IDlinha"];

        $mesmoSentido =
            $origem["Sentido"] === $destino["Sentido"];

        $ordemCorreta =
            $origem["Ordemponto"] < $destino["Ordemponto"];

        if ($mesmaLinha && $mesmoSentido && $ordemCorreta) {

            enviarResultado([
                "linhas" => 1,
                "nome_origem" => $nomeOrigem,
                "nome_destino" => $nomeDestino,
                "linha1" => $origem["IDlinha"],
                "cor1" => $origem["Cor"]
            ]);
        }
    }
}


// ==========================================
// PROCURAR ROTA COM INTEGRAÇÃO (DUAS LINHAS)
// ==========================================

foreach ($rotasOrigem as $primeiraLinha) {

    foreach ($registros as $pontoIntermediario) {

        // Verificar se o ponto está depois da origem
        if (
            $pontoIntermediario["IDlinha"] != $primeiraLinha["IDlinha"] ||
            $pontoIntermediario["Sentido"] !== $primeiraLinha["Sentido"] ||
            $pontoIntermediario["Ordemponto"] <= $primeiraLinha["Ordemponto"]
        ) {
            continue;
        }

        $idConexao = (int)$pontoIntermediario["IDponto"];

        // Não permitir integração na origem ou no destino
        if (
            $idConexao === $pontoOrigem ||
            $idConexao === $pontoDestino
        ) {
            continue;
        }

        foreach ($rotasDestino as $segundaLinha) {

            // Não pode ser a mesma linha
            if ($segundaLinha["IDlinha"] == $primeiraLinha["IDlinha"]) {
                continue;
            }

            foreach ($registros as $registroSegundaLinha) {

                // Procurar o ponto de integração na segunda linha
                $mesmaLinha =
                    $registroSegundaLinha["IDlinha"] == $segundaLinha["IDlinha"];

                $mesmoPonto =
                    (int)$registroSegundaLinha["IDponto"] === $idConexao;

                $mesmoSentido =
                    $registroSegundaLinha["Sentido"] === $segundaLinha["Sentido"];

                $ordemCorreta =
                    $registroSegundaLinha["Ordemponto"] < $segundaLinha["Ordemponto"];

                if (
                    $mesmaLinha &&
                    $mesmoPonto &&
                    $mesmoSentido &&
                    $ordemCorreta
                ) {

                    $nomeConexao = buscarNomePonto($conn, $idConexao);

                    if (!$nomeConexao) {
                        continue;
                    }

                    enviarResultado([
                        "linhas" => 2,
                        "nome_origem" => $nomeOrigem,
                        "nome_destino" => $nomeDestino,
                        "linha1" => $primeiraLinha["IDlinha"],
                        "cor1" => $primeiraLinha["Cor"],
                        "linha2" => $segundaLinha["IDlinha"],
                        "cor2" => $segundaLinha["Cor"],
                        "nome_conexao" => $nomeConexao
                    ]);
                }
            }
        }
    }
}


// ==========================================
// NENHUMA ROTA ENCONTRADA
// ==========================================

erro("Nenhuma rota encontrada entre os pontos selecionados.");

?>
