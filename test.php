<?php

date_default_timezone_set('America/Santiago');
$fecha_actual = new DateTime();
$fecha_inicio= new DateTime('2024-09-23 09:45:32');


// Calcular la diferencia


$diferencia = $fecha_actual->diff($fecha_inicio);

// Obtener la diferencia en segundos

echo json_encode($diferencia);

$segundos = $diferencia->days * 24 * 60 * 60 +
            $diferencia->h * 60 * 60 +
            $diferencia->i * 60 +
            $diferencia->s;
?>