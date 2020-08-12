<?php

namespace hypnodev\Larapal;

use PayPalCheckoutSdk\Core\{
    PayPalHttpClient,
    ProductionEnvironment,
    SandboxEnvironment
};

class Larapal
{
    /**
     * Paypal http client
     *
     * @var PayPalHttpClient
     */
    private PayPalHttpClient $paypalClient;

    /**
     * Larapal constructor.
     */
    public function __construct()
    {
        $credentials = config('larapal.credentials');

        if (config('larapal.mode') == 'sandbox') {
            $environment = new SandboxEnvironment(
                $credentials['sandbox']['client_id'], $credentials['sandbox']['client_secret']
            );
        } else {
            $environment = new ProductionEnvironment( // Paypal docs says "In production, use LiveEnvironment", but this class doesn't exists.
                $credentials['production']['client_id'], $credentials['production']['client_secret']
            );
        }

        $this->paypalClient = new PayPalHttpClient($environment);
    }

    /**
     * Return PayPal Http Client
     *
     * @return PayPalHttpClient
     */
    public function getPaypalClient(): PayPalHttpClient
    {
        return $this->paypalClient;
    }
}
