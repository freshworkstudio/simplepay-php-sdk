# SimplePay PHP SDK
Librería para la integración de SimplePay. Actualmente solo se soporta el pago con Chauchas. Esta librería es mantenida por Gonzalo De Spirito de [freshworkstudio.com](http://freshworkstudio.com) y [simplepay.cl](http://simplepay.cl).


# Installation
```bash
composer require freshwork/simplepay-sdk
```


## Getting Started

### Ejemplo
```php
include 'vendor/autoload.php';

use SimplePay\SimplePay;

$simplePay = new SimplePay('API_KEY');
$amount = 100;
$currency = 'CLP'; //ó CHA
$order_id = 123; //Id de transacción de tu sistema
$description = ''; //opcional: descripción de la compra
$response_url = 'https://misitio.cl/simplepay/response';
$final_url = 'https://misitio.cl/simplepay/final';
$ipn_url = 'https://misitio.cl/simplepay/ipn'; //Necesario solo para el pago de Chauchas
$transaction = $simplePay->initTransaction(SimplePay::PAYMENT_METHOD_CHAUCHAS, $amount, $currency, $order_id, $response_url, $final_url, $description, $ipn_url);

header('Location: ' . $transaction['redirect_url']);
exit;
```

### Proceso
Este código inicia la transacción en el sistema y entrega una URL a la que se debe redireccional al usuario. 
Cuando el usuario paga satisfactoriamente, este es redireccionado a `$response_url` con el token de la transacción enviado por POST. 

### /response
```php
<?php

include '../vendor/autoload.php';

use SimplePay\SimplePay;

$simplePay = new SimplePay('88LCjCRBwOZQQsgcQFYpNL6CoSdv5gOXVDBr2mYm0CgCHO6HDIMT15cD3b83');
$result = $simplePay->getTransactionResult($_POST['token']);

if (is_null($result['transaction']['accepted_at'])) {
    //El pago no fue aceptado. Cancelar la orden, etc. 
    
} else {
	//Si estoy acá es porque accepted_at no es nulo y la compra fue aceptada. Marcar la orden de compra como en espera. 
	//En este punto, si el usuario pagó con chauchas, se recibieron las transacciones, pero no se han confirmado todavía. LLegará una notificación a la URL IPN (paso anterior) indicando cuando esté confirmada. 

	//Marcar orden como pagada y en espera. No despachar productos todavía. Esperar IPN. 

}

$simplePay->acknowledgeTransaction($_POST['token']);

header('Location: ' . $result['redirect_url']);
exit;

```

### Página de Gracias ó rechazo. 
Luego de esto, el usuario es redirigido a l apágina de gracias de Simplepay y posteriormente enviado a `$final_url` (también incluyendo un TOKEN de la transacción por POST). Al llegar a esta URL, el sistema debe obtener los datos de la transacción y leer el ID de la orden. Con eso podrá saber si la orden por la qu ellegó el usuario está aceptada o rechazada. 

### /ipn - NOtificación IPN
Cuando la compra esté confirmada por la red o haya sido 100% confirmada, se notificará a esta URL. 
A diferencia de las otras URL, donde era el navegador del usuario el que entraba, en este caso es el servidor de SimplePay el que se conecta con esta URL directamente. Eso siginfica que si estás probando con una URL local (ejemplo: http://localhost) esta notificación no te llegará. 

Esta página debe imprimir el UUID de la transacción para que Simplepay sepa que se recibió el mensaje correctamente. 
```php
if (!isset($_POST['token'])) {
    die('SimplePay: No se ha podido procesar esta solicitud. ');
}

$token = $_POST['token'];

try {
    $response = $this->simplepay_sdk->getTransactionResult($token);
} catch (Exception $e) {
    die('Error obteniendo información de la transacción: ' . $e->getMessage());
}

if ($response['transaction']['completed_at'] !== null) {
    $order = new WC_Order($response['transaction']['commerce_order_id']);
    $order->add_meta_data('simplepay_completed', true);
    $order->payment_complete($response['transaction']['uuid']);
    $order->add_order_note('Transacción completa y confirmada por la red');
    die($response['transaction']['uuid']);
}

``
