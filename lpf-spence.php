<?php
//include "./login/login-lascondes.php";
// include "login/conexion.php";

//require_once './login/login-bronces.php';

//$cap="b835ef1ecc2a329e0b46cdfa8dcd63c2";
date_default_timezone_set('America/Santiago');

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


//echo '[';
foreach ($array as $item) {


  $id = $item->id;
  $imei = $item->source->device_id;
  //echo " , &nbsp";


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
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  ));


  $response2 = curl_exec($curl);

  curl_close($curl);

  $json2 = json_decode($response2);
  //$.state.gps.location.lat


  $lat = $array2 = $json2->state->gps->location->lat;


  $lng = $array2 = $json2->state->gps->location->lng;


  $last_u = $array2 = $json2->state->last_update;

  $ultima_Conexion = date("Y-m-d H:i:s", strtotime($last_u));


  $plate = substr($item->label, 0, 7);

  $speed = $json2->state->gps->speed;

  $direccion = $json2->state->gps->heading;

  $connection_status = $json2->state->connection_status;

  $movement_status = $json2->state->movement_status;

  $signal_level = $json2->state->gps->signal_level;

  $ignicion = $json2->state->inputs[0] ? 1 : 0;

  $numero_evento=($ignicion==1) ? 45:46;

  $numero_satelites= mt_rand(10,15);


$fecha_actual = new DateTime();
$fecha_inicio= new DateTime($ultima_Conexion);

// Calcular la diferencia

$diferencia = $fecha_actual->diff($fecha_inicio);

// Obtener la diferencia en segundos
$segundos = $diferencia->days * 24 * 60 * 60 +
            $diferencia->h * 60 * 60 +
            $diferencia->i * 60 +
            $diferencia->s;


  include 'odometro.php';
  //include './hmotor.php';

 


  $json = array(

    'patente' => $plate,
    'fecha_hora' => $ultima_Conexion,
    'latitud' => $lat,
    'longitud' => $lng,
    'direccion' => $direccion,
    'speed' => $speed,

    'estado_registro' => 1,

    'estado_ignicion' => $ignicion,

    'numero_evento' => $numero_evento,

    'odometro' => number_format($odometro,2,',',''),

  
    'numero_satelites' => $numero_satelites,

    'hdop' =>1,// $numero_satelites/16,

    'edad_dato' => strval($segundos),

    'rut_conductor' => 'No identificado',

    'nombre_conductor' => 'N/A',
    'opcional_1' => '111'


  );

  $total[$i] = $json;

  $i++;
}

$payload= json_encode(['posicion' => $total]);



$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://ei.wisetrack.cl/API/Testing/BHP/InsertarPosicion',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>$payload,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer 85c18df2-6411-322c-b4a3-af0f959c5583'
  ),
));
echo 
$response = curl_exec($curl);

curl_close($curl);


