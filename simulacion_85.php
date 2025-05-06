<?php

//date_default_timezone_set('America/Santiago');
date_default_timezone_set('UTC');

$user = "spence";


$pasw = "123";

include "login/conexion.php";

$consulta = "SELECT hash FROM masgps.hash where user='$user' and pasw='$pasw'";

$resutaldo = mysqli_query($mysqli, $consulta);

$data = mysqli_fetch_array($resutaldo);

$hash = $data['hash'];

$cap = $hash;


//header("refresh:2");
$listado = '';
$i = 0;
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/list',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => '{"hash":"' . $cap . '"}',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json, text/plain, */*',
    'Accept-Language: es-419,es;q=0.9,en;q=0.8',
    'Connection: keep-alive',
    'Content-Type: application/json',
    'Cookie: _ga=GA1.2.728367267.1665672802; locale=es; _gid=GA1.2.967319985.1673009696; _gat=1; session_key=5d7875e2bf96b5966225688ddea8f098',
    'Origin: http://www.trackermasgps.com',
    'Referer: http://www.trackermasgps.com/',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36'
  ),
));

$response2 = curl_exec($curl);

$json = json_decode($response2);

$array = $json->list;

Loop:

$inicio_loop = microtime(true); // ⏱ Inicio del tiempo

$chunks = array_chunk($array, 10);

foreach ($chunks as $chunk) {
  $total = [];
  $i = 0;

  foreach ($chunk as $item) {
    $id = $item->id;
    $imei = $item->source->device_id;

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/get_state',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{"hash": "' . $cap . '", "tracker_id": ' . $id . '}',
      CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    ));

    $response2 = curl_exec($curl);
    curl_close($curl);

    $json2 = json_decode($response2);

    // Si json2 no está definido o malformado, sigue al siguiente
    if (!isset($json2->state)) {
      continue;
    }

    $lat = $json2->state->gps->location->lat ?? null;
    $lng = $json2->state->gps->location->lng ?? null;
    $last_u = $json2->state->last_update ?? null;
    $ultima_Conexion = date("Y-m-d H:i:s", strtotime($last_u));

    $plate = substr($item->label, 0, 7);
    $speed = $json2->state->gps->speed ?? 0;
    $direccion = $json2->state->gps->heading ?? 0;
    $connection_status = $json2->state->connection_status ?? '';
    $movement_status = $json2->state->movement_status ?? '';
    $signal_level = $json2->state->gps->signal_level ?? 0;
    $ignicion = $json2->state->inputs[0] ?? false ? 1 : 0;
    $numero_evento = $ignicion == 1 ? 45 : 46;
    $numero_satelites = mt_rand(10, 15);

    $fecha_actual = new DateTime();
   // $fecha_inicio = new DateTime($ultima_Conexion);
    $fecha_inicio = new DateTime();
    $diferencia = $fecha_actual->diff($fecha_inicio);
    $segundos = $diferencia->days * 86400 +
                $diferencia->h * 3600 +
                $diferencia->i * 60 +
                $diferencia->s;

  //  include 'odometro.php';
    include 'driver.php';
    include 'giroscopio.php';

    $rut_sin_guion = str_replace('-', '', $rut);
    $plate_sin_guion = str_replace('-', '', $plate);

  //   if (strtoupper($plate_sin_guion) === 'KZGH85') {
  //     $numero_evento = 45;
  //     $ignicion = 1;
     
  // }

  $numero_evento = 45;
       $ignicion = 1;

  $key_button8=substr($key_button, -8);

    $json = [
      'patente' => $plate_sin_guion,
      //'fecha_hora' => $ultima_Conexion,
      'fecha_hora' => date("Y-m-d H:i:s"),
      'latitud' => $lat,
      'longitud' => $lng,
      'direccion' => $direccion,
      'velocidad' => $speed,
      'estado_registro' => 1,
      'estado_ignicion' => $ignicion,
      'numero_evento' => $numero_evento,
     'odometro' =>  $odometerValue,
      'numero_satelites' => $numero_satelites,
      'hdop' => 1,
      'edad_dato' => strval($segundos),
      'rut_conductor' => $key_button8,
      'nombre_conductor' => $rut_sin_guion
      //'name'=>$fullName
      //'opcional_1' => $axisXValue/200
      
    ];

    $total[$i] = $json;
    $i++;
  }

  if (!empty($total)) {
    echo 
    $payload = json_encode(['posicion' => $total]);

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://gw.wisetrack.cl/BHP/1.0.0/InsertarPosicion',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $payload,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer cf37bd88-78b8-4b5d-94d2-d3145f6480db'
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    echo $response;
  }

  // Si deseas evitar saturar el servidor
  sleep(1); // Espera 1 segundo entre lotes
}

$fin_loop = microtime(true); // ⏱ Fin del tiempo

$tiempo_total = $fin_loop - $inicio_loop;
$numero_tramas = count($total);
echo "Tiempo  del loop: " . round($tiempo_total, 2) . " segundos ". ". Total  de Tramas enviadas : $numero_tramas\n";






//goto Loop;

?>
