<?php

namespace GatewayPayment\Config;

use CodeIgniter\Config\BaseConfig;

class GatewayConfig extends BaseConfig
{
    /**
     * The default payment gateway to use.
     *
     * @var string
     */
    public $defaultGateway;

    /**
     * The default currency for transactions.
     *
     * @var string
     */
    public $defaultCurrency;

    /**
     * Configuration for each payment gateway.
     *
     * @var array
     */
    public $gateways;

    public function __construct()
    {
        parent::__construct();

        $this->defaultGateway = env('DEFAULT_GATEWAY', 'AuthorizeNet');
        $this->defaultCurrency = env('DEFAULT_CURRENCY', 'USD');

        $this->gateways = [
            'AuthorizeNet' => [
                'api_login_id' => env('AUTHORIZENET_API_LOGIN_ID', ''),
                'transaction_key' => env('AUTHORIZENET_TRANSACTION_KEY', ''),
                'sandbox' => env('AUTHORIZENET_SANDBOX', true),
                'supported_currencies' => ['USD', 'CAD'],
                'supported_transaction_types' => [
                    'authCaptureTransaction',
                    'authOnlyTransaction',
                    'captureTransaction',
                    'refundTransaction',
                    'voidTransaction',
                ],
            ],
            'Stripe' => [
                'secret_key' => env('STRIPE_SECRET_KEY', ''),
                'sandbox' => env('STRIPE_SANDBOX', true), // Use test keys for sandbox
                'supported_currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'],
                'supported_transaction_types' => [
                    'charge',
                    'refund',
                    'customer_create',
                ],
            ],
            'PayPal' => [
                'client_id' => env('PAYPAL_CLIENT_ID', ''),
                'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
                'sandbox' => env('PAYPAL_SANDBOX', true),
                'supported_currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'],
                'supported_transaction_types' => [
                    'order_create',
                    'order_capture',
                    // 'refund' and 'customer_create' to be added when implemented
                ],
            ],
        ];
    }
}