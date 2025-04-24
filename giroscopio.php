<?php


$curl = curl_init();

//$id=10302375;
//$cap='f2c323c59011cb7a3f75bd6fab8c6d76';




curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/readings/list',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{"tracker_id":'.$id.',"hash":"'.$cap.'"}',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json, text/plain, */*',
    'Accept-Language: es-419,es;q=0.9,en;q=0.8',
    'Connection: keep-alive',
    'Content-Type: application/json',
    'Cookie: _ga=GA1.2.252064098.1712071785; _ga_XXFQ02HEZ2=GS1.2.1727273013.28.1.1727273760.0.0.0; locale=es; _gid=GA1.2.121752548.1727788444; session_key=f2c323c59011cb7a3f75bd6fab8c6d76; check_audit=f2c323c59011cb7a3f75bd6fab8c6d76; _gat=1',
    'NVX-ISO-DateTime: true',
    'Origin: http://www.trackermasgps.com',
    'Referer: http://www.trackermasgps.com/',
    'User-Agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Mobile Safari/537.36'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
//echo $response;

$data = json_decode($response, true);

// Extraer los valores de "value" para los ejes X, Y y Z y el odómetro
$axisXValue = $data['inputs'][1]['value'] ?? '';
$axisYValue = $data['inputs'][3]['value'] ?? '';
$axisZValue = $data['inputs'][2]['value'] ?? '';
$odometerValue = $data['counters'][0]['value'] ?? '';
$odometerValue=floatval(str_replace(',', '', $odometerValue));
$odometerValue=number_format($odometerValue, 3, '.', '');

// Imprimir los valores
/*
echo "Valor de Eje X: " . $axisXValue . "\n";
echo "Valor de Eje Y: " . $axisYValue . "\n";
echo "Valor de Eje Z: " . $axisZValue . "\n";
echo "Valor del Odómetro: " . $odometerValue . "\n";
*/