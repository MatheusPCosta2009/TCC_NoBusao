<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
        $optPartida = $_POST["partida"];
        $optDestino = $_POST["destino"];

        function CalcularDistancia(float $latitudePartida, float $longitudePartida, float $latitudeDestino, float $longitudeDestino): void{
            $latitudePartida;
            $longitudePartida;
            $latitudeDestino;
            $longitudeDestino;
            $latitudePartida = deg2rad($latitudePartida);
            $longitudePartida = deg2rad($longitudePartida);
            $latitudeDestino = deg2rad($latitudeDestino);
            $longitudeDestino = deg2rad($longitudeDestino);
            $deltaLatitude = $latitudeDestino - $latitudePartida;
            $deltaLongitude = $longitudeDestino - $longitudePartida;
            $a = sin($deltaLatitude / 2) ** 2 + cos($latitudePartida) * cos($latitudeDestino) * sin($deltaLongitude / 2) ** 2;
            $b = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distancia = $b * 6371;
            echo number_format($distancia, 2, ',', '.')."km";
        }
        CalcularDistancia(-21.2489797890941, -50.314986017318766, -21.267457575052017, -50.30568411575291);
    ?>
</body>
</html>
