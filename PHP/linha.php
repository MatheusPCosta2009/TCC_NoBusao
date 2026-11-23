
<?php

header("Content-Type: application/json; charset=UTF-8");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function responder($sucesso, $mensagem, $dados = [], $status = 200) {

    http_response_code($status);

    echo json_encode(
        array_merge([
            "sucesso" => $sucesso,
            "mensagem" => $mensagem
        ], $dados),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

try {

    require_once __DIR__ . "/conexao.php";

    $dados = json_decode(file_get_contents("php://input"), true);

    if (!is_array($dados)) {
        responder(false, "Dados enviados em formato inválido.", [], 400);
    }

    $acao = $dados["acao"] ?? "";

    /* ==========================================
       LISTAR PONTOS
    ========================================== */

    if ($acao === "listar_pontos") {

        $sql = "SELECT ID, Nome FROM ponto ORDER BY Nome";

        $resultado = $conn->query($sql);

        $pontos = [];

        while ($linha = $resultado->fetch_assoc()) {
            $pontos[] = $linha;
        }

        responder(true, "Pontos encontrados.", [
            "pontos" => $pontos
        ]);
    }

    /* ==========================================
       LISTAR LINHAS
    ========================================== */

    if ($acao === "listar_linhas") {

        $sql = "
            SELECT
                linha.ID,
                linha.Cor,
                COUNT(ponto_linha.ID) AS total_pontos
            FROM linha
            LEFT JOIN ponto_linha
                ON ponto_linha.IDlinha = linha.ID
            GROUP BY linha.ID, linha.Cor
            ORDER BY linha.ID
        ";

        $resultado = $conn->query($sql);

        $linhas = [];

        while ($linha = $resultado->fetch_assoc()) {
            $linhas[] = $linha;
        }

        responder(true, "Linhas encontradas.", [
            "linhas" => $linhas
        ]);
    }

    /* ==========================================
       CADASTRAR LINHA
    ========================================== */

    if ($acao === "cadastrar") {

        $numero = trim((string)($dados["numero"] ?? ""));
        $cor = trim((string)($dados["cor"] ?? ""));
        $pontos = $dados["pontos"] ?? [];

        if (
            $numero === "" ||
            !ctype_digit($numero) ||
            (int)$numero <= 0 ||
            (int)$numero > 2147483647
        ) {
            responder(false, "Informe um número válido para a linha.", [], 400);
        }

        $numero = (int)$numero;

        if ($cor === "" || mb_strlen($cor) > 20) {
            responder(false, "Informe uma cor com até 20 caracteres.", [], 400);
        }

        if (!is_array($pontos) || count($pontos) === 0) {
            responder(false, "Adicione os pontos da linha.", [], 400);
        }

        $temIda = false;
        $temVolta = false;

        foreach ($pontos as $ponto) {

            $sentido = $ponto["sentido"] ?? "";

            if ($sentido === "Ida") {
                $temIda = true;
            }

            if ($sentido === "Volta") {
                $temVolta = true;
            }

            if (
                !isset($ponto["idPonto"]) ||
                filter_var($ponto["idPonto"], FILTER_VALIDATE_INT) === false ||
                (int)$ponto["idPonto"] <= 0
            ) {
                responder(false, "Existe um ponto inválido no cadastro.", [], 400);
            }

            if (!in_array($sentido, ["Ida", "Volta"], true)) {
                responder(false, "Sentido de trajeto inválido.", [], 400);
            }

            if (
                !isset($ponto["horarios"]) ||
                !is_array($ponto["horarios"])
            ) {
                responder(false, "A lista de horários está inválida.", [], 400);
            }
        }

        if (!$temIda || !$temVolta) {
            responder(false, "Cadastre pelo menos um ponto na Ida e um na Volta.", [], 400);
        }

        $conn->begin_transaction();

        try {

            /* Cadastra a linha com o número informado */

            $sqlLinha = "INSERT INTO linha (ID, Cor) VALUES (?, ?)";

            $stmtLinha = $conn->prepare($sqlLinha);

            $stmtLinha->bind_param("is", $numero, $cor);

            $stmtLinha->execute();

            /* Cadastra os pontos e seus horários */

            $sqlPonto = "
                INSERT INTO ponto_linha
                    (IDlinha, IDponto, Ordemponto, Sentido)
                VALUES (?, ?, ?, ?)
            ";

            $stmtPonto = $conn->prepare($sqlPonto);

            $sqlHorario = "
                INSERT INTO horario (IDpontolinha, Hora)
                VALUES (?, ?)
            ";

            $stmtHorario = $conn->prepare($sqlHorario);

            $ordens = [
                "Ida" => 0,
                "Volta" => 0
            ];

            foreach ($pontos as $ponto) {

                $idPonto = (int)$ponto["idPonto"];
                $sentido = $ponto["sentido"];

                $ordens[$sentido]++;

                $ordem = $ordens[$sentido];

                $stmtPonto->bind_param(
                    "iiis",
                    $numero,
                    $idPonto,
                    $ordem,
                    $sentido
                );

                $stmtPonto->execute();

                $idPontoLinha = $conn->insert_id;

                foreach ($ponto["horarios"] as $hora) {

                    $hora = trim((string)$hora);

                    if ($hora === "") {
                        continue;
                    }

                    if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
                        throw new Exception("Um dos horários está inválido.");
                    }

                    $horaCompleta = $hora . ":00";

                    $stmtHorario->bind_param(
                        "is",
                        $idPontoLinha,
                        $horaCompleta
                    );

                    $stmtHorario->execute();
                }
            }

            $conn->commit();

            responder(true, "Linha cadastrada com sucesso.");

        } catch (Throwable $erro) {

            $conn->rollback();

            if (
                $erro instanceof mysqli_sql_exception &&
                $erro->getCode() == 1062
            ) {
                responder(false, "Já existe uma linha cadastrada com esse número.", [], 409);
            }

            if (
                $erro instanceof mysqli_sql_exception &&
                $erro->getCode() == 1452
            ) {
                responder(false, "Um dos pontos selecionados não existe no banco de dados.", [], 400);
            }

            throw $erro;
        }
    }

    /* ==========================================
       EXCLUIR LINHA
    ========================================== */

    if ($acao === "excluir") {

        $numero = $dados["numero"] ?? null;

        if (
            filter_var($numero, FILTER_VALIDATE_INT) === false ||
            (int)$numero <= 0
        ) {
            responder(false, "Número da linha inválido.", [], 400);
        }

        $numero = (int)$numero;

        $sql = "DELETE FROM linha WHERE ID = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $numero);

        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            responder(false, "Linha não encontrada.", [], 404);
        }

        responder(true, "Linha excluída com sucesso.");
    }

    responder(false, "Ação não reconhecida.", [], 400);

} catch (Throwable $erro) {

    error_log("Erro em linha.php: " . $erro->getMessage());

    responder(
        false,
        "Ocorreu um erro no servidor. Verifique o arquivo de erros do PHP.",
        [],
        500
    );
}
