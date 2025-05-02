<?php

date_default_timezone_set('America/Santiago');

$user = "spence";
$pasw = "123";

include "login/conexion.php";

$consulta = "SELECT hash FROM masgps.hash WHERE user='$user' AND pasw='$pasw'";
$resutaldo = mysqli_query($mysqli, $consulta);
$data = mysqli_fetch_array($resutaldo);
$hash = $data['hash'];
$cap=$hash;
while (true) {
    $inicio_loop = microtime(true);
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/list',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['hash' => $hash]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ));
    $response2 = curl_exec($curl);
    curl_close($curl);

    $json = json_decode($response2);
    if (!$json || !isset($json->list)) {
        echo "Error al obtener lista de trackers.\n";
        sleep(3600);
        continue;
    }

    $array = $json->list;
    $chunks = array_chunk($array, 10);

    foreach ($chunks as $chunk) {
        $total = [];
        $i = 0;

        foreach ($chunk as $item) {
            $id = $item->id;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/get_state',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode(['hash' => $hash, 'tracker_id' => $id]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ));
            $response2 = curl_exec($curl);
            curl_close($curl);

            $json2 = json_decode($response2);
            if (!isset($json2->state)) continue;

            $lat = $json2->state->gps->location->lat ?? null;
            $lng = $json2->state->gps->location->lng ?? null;
            $last_u = $json2->state->last_update ?? null;
            $plate = substr($item->label, 0, 7);
            $speed = $json2->state->gps->speed ?? 0;
            $direccion = $json2->state->gps->heading ?? 0;
            $ignicion = $json2->state->inputs[0] ?? false ? 1 : 0;
            $numero_satelites = mt_rand(10, 15);

            include 'driver.php';
            include 'giroscopio.php';

            $rut_sin_guion = str_replace('-', '', $rut);
            $plate_sin_guion = str_replace('-', '', $plate);

            $opcional_random = mt_rand(700, 740) / -100;

            $json = [
                'patente' => $plate_sin_guion,
                'fecha_hora' => date("Y-m-d H:i:s"),
                'latitud' => $lat,
                'longitud' => $lng,
                'direccion' => $direccion,
                'velocidad' => 11,
                'estado_registro' => 1,
                'estado_ignicion' => 1,
                'numero_evento' => 51,
                'odometro' => $odometerValue,
                'numero_satelites' => $numero_satelites,
                'hdop' => 1,
                'edad_dato' => "0",
                'rut_conductor' => $key_button,
                'nombre_conductor' => $rut_sin_guion,
                'opcional_1' => $opcional_random
            ];

            $total[$i++] = $json;
        }

        if (!empty($total)) {
            $payload = json_encode(['posicion' => $total]);

            echo $payload;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://gw.wisetrack.cl/BHP/1.0.0/InsertarPosicion',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer cf37bd88-78b8-4b5d-94d2-d3145f6480db'
                ],
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            echo $response;
        }

        sleep(1); // pausa entre lotes
    }

    $tiempo_total = microtime(true) - $inicio_loop;
    echo "Tiempo total: " . round($tiempo_total, 2) . " seg\n";
    
    // Esperar 1 hora antes de continuar el bucle
    sleep(3600);
}
?>
