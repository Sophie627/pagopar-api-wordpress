<?php
/**
 * Recommended way to include parent theme styles.
 * Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme
 */

add_action('wp_enqueue_scripts', 'theme_enqueue_styles', 998);
function theme_enqueue_styles() {
    wp_enqueue_style('flozen-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('flozen-child-style', get_stylesheet_uri());
}
/**
 * Your code goes below
 */
 
 function callAPI($method, $url, $data){
    $curl = curl_init();
    switch ($method){
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    // OPTIONS:
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // EXECUTE:
    $result = curl_exec($curl);
    if(!$result){die("Connection Failure");}
    curl_close($curl);
    return $result;
}
 
 add_action('rest_api_init', function () {
    register_rest_route( 'api/pagopar', 'iniciar_transaccion', array(
                'methods'  => 'GET',
                'callback' => 'iniciar_transaccion'
      ));
  register_rest_route( 'api/flutter', 'iniciar_transaccion/(?P<user_id>\d+)/(?P<phone_number>\d+)', array(
                'methods'  => 'GET',
                'callback' => 'iniciar_transaccion',
      			'args' => ['user_id', 'phone_number'],
      ));
}); 

function iniciar_transaccion($request) {
    $user_id = $request['user_id'];
    $billing_phone = $request['phone_number'];
    
    $data = '{
        "token": "be95cf6b1863b857311f7577956840f20f341004",
        "comprador": {
            "ruc": "4247903-7",
            "email": "fernandogoetz@gmail.com",
            "ciudad": null,
            "nombre": "Rudolph Goetz",
            "telefono": "0972200046",
            "direccion": "",
            "documento": "4247903",
            "coordenadas": "",
            "razon_social": "Rudolph Goetz",
            "tipo_documento": "CI",
            "direccion_referencia": null
        },
        "public_key": "156c6e3241dc7bef012f846d63759808",
        "monto_total": 100000,
        "tipo_pedido": "VENTA-COMERCIO",
        "compras_items": [
            {
                "ciudad": "1",
                "nombre": "Ticket virtual a evento Ejemplo 2017",
                "cantidad": 1,
                "categoria": "909",
                "public_key": "156c6e3241dc7bef012f846d63759808",
                "url_imagen": "http://www.fernandogoetz.com/d7/wordpress/wp-content/uploads/2017/10/ticket.png",
                "descripcion": "Ticket virtual a evento Ejemplo 2017",
                "id_producto": 895,
                "precio_total": 100000,
                "vendedor_telefono": "",
                "vendedor_direccion": "",
                "vendedor_direccion_referencia": "",
                "vendedor_direccion_coordenadas": ""
            }
        ],
        "fecha_maxima_pago": "2020-11-04 14:14:48",
        "id_pedido_comercio": "111111",
        "descripcion_resumen": ""
    }';

    $make_call = callAPI('POST', 'https://api.pagopar.com/api/comercios/1.1/iniciar-transaccion', $data);
    $response_data = json_decode($make_call, true);

    $response = new WP_REST_Response($response_data);
    $response->set_status(200);
    
    return $response;
}