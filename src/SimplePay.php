<?php

namespace SimplePay;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use SimplePay\Exceptions\UnauthorizedException;

/**
 * Class SimplePay
 * @package SimplePay
 */
class SimplePay
{
    /**
     *
     */
    const PAYMENT_METHOD_CHAUCHAS = 'chauchas';

    /**
     * @var Client|null
     */
    protected $client;

    /**
     * @var string
     */
    protected $baseUrl = 'https://simplepay.cl/api/';

    protected $token = '';

    /**
     * SimplePay constructor.
     * @param null $token
     * @param Client|null $client
     */
    public function __construct($token = null, Client $client = null)
    {
        if ($token) {
            $this->token = $token;
        }
        if (!$client) {
            $client = new Client([
                'base_uri' => $this->baseUrl,
                'timeout' => 30,
                'headers' => [
                    'Accept'     => 'application/json',
                    'User-Agent' => 'SimplePay SDK',
                    'Authorization' => 'Bearer ' . $this->token
                ]
            ]);
        }

        $this->client = $client;
    }

    /**
     * @param $method
     * @param $endpoint
     * @param $data
     * @return array|mixed|object
     * @throws \Exception
     */
    public function execute_call($method, $endpoint, $data)
    {
        $response = null;
        try {
            if (strtolower($method) == 'get') {
                $response = $this->client->get($endpoint, [
                    'query' => $data,
                ]);
            } else {
                $response = $this->client->post($endpoint, [
                    'form_params' => $data,
                ]);
            }
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401) {
                throw new UnauthorizedException('El token de SimplePay no es válido. Contacta al administrador del sitio para que solucione el problema.');
            }

            throw new \SimplePay\Exceptions\ClientException($e->getMessage(), $e->getCode());
        }

        if ($response === null) {
            throw new \Exception('El método "' . $method . '" no está implementado en el SDK');
        }

        return json_decode($response->getBody(), true);
    }

    public function post($endpoint, array $data = null)
    {
        return $this->execute_call('POST', $endpoint, $data);
    }

    public function get($endpoint, array $data = null)
    {
        return $this->execute_call('GET', $endpoint, $data);
    }

    public function initTransaction($payment_method, $amount, $currency, $order_id, $response_url, $final_url, $detail = '', $ipn_url='')
    {
        if ($payment_method === self::PAYMENT_METHOD_CHAUCHAS) {
            if ($ipn_url === '') {
                throw new \Exception('Para el metodo de pago "chauchas" es necesario definir una url IPN donde Simplepay notificará cuando se reciban las confirmaciones de la red');
            }
        }

        return $this->post('transactions', [
            'payment_method' => $payment_method,
            'amount' => $amount,
            'currency' => $currency,
            'order_id' => $order_id,
            'response_url' => $response_url,
            'ipn_url' => $ipn_url,
            'final_url' => $final_url,
            'detail' => $detail,
        ]);

    }

    public function getTransactionResult($token)
    {
        return $this->get('transactions/' . $token  . '/result');
    }

    public function acknowledgeTransaction($token)
    {
        return $this->post('transactions/' . $token . '/acknowledge');
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }
    /**
     * @param null|string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return Client|null
     */
    public function getClient()
    {
        return $this->client;
    }
    /**
     * @param Client|null $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }


}