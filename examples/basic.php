<?php

include '../vendor/autoload.php';

use SimplePay\SimplePay;

$simplePay = new SimplePay('88LCjCRBwOZQQsgcQFYpNL6CoSdv5gOXVDBr2mYm0CgCHO6HDIMT15cD3b83');
$amount = 100;
$currency = 'CLP'; //ó CHA
$order_id = 123; //Id de transacción de tu sistema
$description = ''; //opcional: descripción de la compra
$notify_url = 'https://misitio.cl/simplepay/notify';
$final_url = 'https://misitio.cl/simplepay/final';
$ipn_url = 'https://misitio.cl/simplepay/ipn'; //Necesario solo para el pago de Chauchas
$transaction = $simplePay->initTransaction(SimplePay::PAYMENT_METHOD_CHAUCHAS, $amount, $currency, $order_id, $notify_url, $final_url, $description, $ipn_url);

header('Location: ' . $transaction['redirect_url']);
exit;
