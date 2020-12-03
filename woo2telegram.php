<?php
/**
 * Plugin Name:       WooCommerce&Telegram
 * Plugin URI:        https://github.com/guionazo/woocommerce2telegram
 * Description:       Envia mediante un bot de Telegram los datos de los pedidos entrantes.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Pablo Manuel Castelo Vigo
 */

    add_action('admin_menu', 'teamez_pagina_opciones');

function teamez_pagina_opciones() {
	add_options_page('WooCommerce+Telegram Opciones', 'WooCommerce+Telegram', 'manage_options', __FILE__, 'teamez_mostrar_pagina_opciones');
	add_action( 'admin_init', 'teamez_registrar_opciones' );
}


function teamez_registrar_opciones() {
	register_setting( 'teamez-opciones', 'opcion_id_bot' );
	register_setting( 'teamez-opciones', 'opcion_token' );
}

function teamez_mandartelegram($mensaje){
    $token = get_option('opcion_token'); $urlMsg = "https://api.telegram.org/bot{$token}/sendMessage";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlMsg); curl_setopt($ch, CURLOPT_POST, 1); curl_setopt($ch, CURLOPT_POSTFIELDS, "chat_id=".get_option('opcion_id_bot')."&parse_mode=HTML&text=$mensaje"); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); $server_output = curl_exec($ch);
    curl_close($ch);
}

function teamez_mostrar_pagina_opciones() {
?>
<div class="wrap">
<h1>OPCIONES WooCommerce+Telegram</h1>

<form method="post" action="options.php">
    <?php settings_fields( 'teamez-opciones' ); ?>
    <?php do_settings_sections( 'teamez-opciones' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">TELEGRAM ID Destinatario de los mensajes</th>
        <td><input type="text" name="opcion_id_bot" value="<?php echo esc_attr( get_option('opcion_id_bot') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">TELEGRAM TOKEN Token de tu bot de Telegram</th>
        <td><input type="text" name="opcion_token" value="<?php echo esc_attr( get_option('opcion_token') ); ?>" /></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php } ?>
<?php
    do_action( 'woocommerce_checkout_order_processed', $order_id );
function teamez_enviar_telegram( $order_id ) { 
    $order = wc_get_order( $order_id );
    $order_data = $order->get_data(); // The Order data
    $comanda = 'Num. de pedido: ' . $order_data['id'] . ' Fecha: ' . $order_data['date_created']->date('Y-m-d H:i:s') . "\n" . "\n";
    $num_articulos = 0;
    foreach ($order->get_items() as $item_key => $item ):
        $item_data    = $item->get_data();
    $num_articulos = $num_articulos + $item_data['quantity'];
    $armarcomanda = $item_data['name'] . ' // Cantidad: ' . $item_data['quantity'] . ' uds' . "\n";
    $comanda .= $armarcomanda;
    endforeach;
    $direccion = get_option('woocommerce_ship_to_destination');
    if ($order_data['customer_note'] != "") { $notas_pedido = 'Notas del pedido: ' . $order_data['customer_note'] . "\n" . "\n"; }
    $msgrelleno = "@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@";
    $composicion = $msgrelleno . "\n" . "¡¡Ha entrado un pedido!!" . "\n" . "\n" . $comanda . "\n" . 'Total del pedido: ' . $order_data['total'] . ' ' . $order_data['currency'] . '  Número de articulos: ' . $num_articulos . "\n" . "\n" . 'Cliente: ' . $order_data[$direccion]['first_name'] . ' ' . $order_data[$direccion]['last_name'] . "\n" .'Dirección de entrega: ' .  $order_data[$direccion]['address_1'] . ' ' . $order_data[$direccion]['address_2'] . ', ' . $order_data[$direccion]['city'] . ' ' . $order_data[$direccion]['postcode'] . "\n" . "\n"; . 'Telefono: ' . $order_data[$direccion]['phone'] . "\n" . "\n" . $notas_pedido . $msgrelleno . " pedido entrante";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot".get_option('opcion_token')."/sendMessage"); curl_setopt($ch, CURLOPT_POST, 1); curl_setopt($ch, CURLOPT_POSTFIELDS, "chat_id=".get_option('opcion_id_bot')."&parse_mode=HTML&text=$composicion"); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); $server_output = curl_exec($ch);
    curl_close($ch);
}; 
function teamez_cancelar_envio ( $order_id ) {
    //Obtiene la orden y usa las funciones de mandaravanza y mandartelegram
    $order = wc_get_order( $order_id );
    $order_data = $order->get_data(); // The Order data
    $direccion = get_option('woocommerce_ship_to_destination');
    $msgrelleno = "@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@";
    $mensaje = $msgrelleno . "\n" . 'Pedido número ' . $order_data['id'] . ' Fecha: ' . $order_data['date_created']->date('Y-m-d H:i:s') . "\n" . ' CANCELADO!!!' . "\n" . 'EL PEDIDO HA SIDO CANCELADO' . "\n" . 'Cliente: ' . $order_data[$direccion]['first_name'] . ' ' . $order_data[$direccion]['last_name'] . "\n" .'Dirección de entrega: ' .  $order_data[$direccion]['address_1'] . ' ' . $order_data[$direccion]['address_2'] . ', ' . $order_data[$direccion]['city'] . ' ' . $order_data[$direccion]['postcode'] . "\n" . "\n" . 'Telefono: ' . $order_data[$direccion]['phone'] . "\n" . $msgrelleno;
    teamez_mandartelegram($mensaje);
};
add_action( 'woocommerce_checkout_order_processed', 'teamez_enviar_telegram', 10, 1 );
add_action( 'woocommerce_order_status_cancelled', 'teamez_cancelar_envio', 10, 1 ); 
?>
