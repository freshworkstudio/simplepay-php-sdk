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


