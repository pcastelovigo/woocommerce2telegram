<?php
//Version 1.1 25/09/2020 (desde 1.0 1/9/2020)
//ARRAY DE ADMINISTRACIÓN
$mastertelegram = "-0000000000";
$arrayclientes = array("test-site", "test6", "test5");
$arraytelegram = array("-00000000", "-00000000", "-00000000"); //para implementar comandos para los diferentes clientes

//Calcula las fechas del mes anterior, obtenida directamente de las funciones de woocommerce
$first_day_current_month = strtotime( date( 'Y-m-01' ) );
$start_date = strtotime( date( 'Y-m-01', strtotime( '-1 DAY', $first_day_current_month ) ) );
$end_date = strtotime( date( 'Y-m-t', strtotime( '-1 DAY', $first_day_current_month ) ) );

//Variables adicionales, $path sobra, queda auxiliar
$path = "https://api.telegram.org/bot0000000000:FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";
//recoge el churro
$update = json_decode(file_get_contents("php://input"), TRUE);
//chatid es la id del chat, mensaje es el mensaje del chat. El id del chat y el mensaje, directamente, no te confundas
$chatId = $update["message"]["chat"]["id"];
$message = $update["message"]["text"];



//función que envia las respuestas
function teamez_mandartelegram($reporte, $telegramid){
    $token = "0000000000:FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF"; $urlMsg = "https://api.telegram.org/bot{$token}/sendMessage";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlMsg); curl_setopt($ch, CURLOPT_POST, 1); curl_setopt($ch, CURLOPT_POSTFIELDS, "chat_id=".$telegramid."&parse_mode=HTML&text=$reporte"); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); $server_output = curl_exec($ch);
    curl_close($ch);
}

//función que obtiene los datos de los restaurantes de la API de woocommerce
function teamez_pedidorestaurante($clientes){
global $first_day_current_month; global $start_date; global $end_date;
$url = "https://www.ejemplo.com/{$clientes}/wp-json/wc/v3/reports/orders/totals?date_min={$start_date}&date_max={$end_date}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_USERPWD, "ck_FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF:cs_FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF"); curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); $server_output = curl_exec($ch);
return json_decode($server_output, true);
}


//define el comando administrador. Esto quedó listo! todo: añadir cuentas este mes
if (strpos($message, "/administrador") === 0 && ($chatId == $mastertelegram)) {
$reporte = ''; //Evita el warning undefined variable dos lineas más abajo
$total_pedidos = 0;
foreach ($arrayclientes as  $clientes):
$datosreporte = teamez_pedidorestaurante($clientes); if (isset($datosreporte[3]['total']) == true) {$total_pedidos = $total_pedidos + $datosreporte[3]['total']; } else { $datosreporte[3]['total'] = "ningún"; }
$reporte .= 'Restaurante: ' . $clientes . ' Pedido(s) completados del mes: ' . strftime("%B", substr( $start_date , 0, 10 ) ) . ' ' . $datosreporte[3]['total'] . ' pedido(s)' . "\n";
endforeach; $reporte .= "\n" . "TOTAL PEDIDOS COMPLETADOS: " . $total_pedidos . " pedido(s)";
$telegramid = $mastertelegram;
teamez_mandartelegram($reporte, $telegramid);
}
?>
