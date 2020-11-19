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
    register_rest_route( 'api/flutter', 'iniciar_transaccion/(?P<buyer_email>\d+)/(?P<buyer_name>\d+)/(?P<total>\d+)/(?P<shopping_items>\d+)', array(
                'methods'  => 'GET',
                'callback' => 'iniciar_transaccion',
      ));
}); 

function iniciar_transaccion($request) {
    $public_token = '156c6e3241dc7bef012f846d63759808';
    $private_token = 'd6ef2d40d05f2e8e225e94031f0bb550';
    $order_id = str_replace(" ", "", substr(strval(microtime()), 2));
    $price = $request['total'];

    $token = sha1($private_token . $order_id . $price);

    $buyer_email = $request['buyer_email'];
    $buyer_name = $request['buyer_name'];
    $shopping_items = $request['shopping_items'];

    $data = '{
        "token": "'. $token. '",
        "comprador": {
            "ruc": "",
            "email": "'. $buyer_email. '",
            "ciudad": null,
            "nombre": "'. $buyer_name. '",
            "telefono": "",
            "direccion": "",
            "documento": "'. $order_id. '",
            "coordenadas": "",
            "razon_social": "",
            "tipo_documento": "CI",
            "direccion_referencia": null
        },
        "public_key": "'. $public_token . '",
        "monto_total": '. $price. ',
        "tipo_pedido": "VENTA-COMERCIO",
        "compras_items": [
            '. $shopping_items. '
        ],
        "fecha_maxima_pago": "2099-12-04 14:14:48",
        "id_pedido_comercio": "'. $order_id. '",
        "descripcion_resumen": ""
    }';

    $make_call = callAPI('POST', 'https://api.pagopar.com/api/comercios/1.1/iniciar-transaccion', $data);
    $response_data = json_decode($make_call, true);

    $response = new WP_REST_Response($response_data);
    $response->set_status(200);
    
    return $response;
}