<?php

namespace GatewayPayment\Gateways\PayPal;

use GatewayPayment\Entities\Payment;
use GatewayPayment\Entities\Customer;
use GatewayPayment\Entities\PaymentResponse;
use GatewayPayment\Exceptions\PaymentException;
use GatewayPayment\Interfaces\PaymentGatewayInterface;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PayPalGateway implements PaymentGatewayInterface
{
    private PayPalHttpClient $client;
    private PayPalMapper $mapper;
    private ErrorCodeHandler $errorHandler;

    public function __construct(array $config)
    {
        $environment = new SandboxEnvironment($config['client_id'], $config['client_secret']);
        $this->client = new PayPalHttpClient($environment);
        $this->mapper = new PayPalMapper();
        $this->errorHandler = new ErrorCodeHandler();
    }

    /**
     * Charge a payment.
     *
     * @param Payment $payment
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function charge(Payment $payment): PaymentResponse
    {
        try {
            // Create order
            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');
            $request->body = $this->mapper->mapPaymentToOrder($payment);

            $order = $this->client->execute($request)->result;

            // Capture order
            $captureRequest = new OrdersCaptureRequest($order->id);
            $captureRequest->prefer('return=representation');

            $capture = $this->client->execute($captureRequest)->result;

            return $this->mapper->mapResponseToPaymentResponse($capture);
        } catch (\Exception $e) {
            $this->errorHandler->handle($e);
        }
    }

    /**
     * Refund a payment.
     *
     * @param string $transactionId
     * @param float $amount
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function refund(string $transactionId, float $amount): PaymentResponse
    {
        try {
            // PayPal refund requires custom implementation (not directly supported in v2 SDK)
            throw new PaymentException('PayPal refund not implemented yet');
        } catch (\Exception $e) {
            $this->errorHandler->handle($e);
        }
    }

    /**
     * Create a customer profile.
     *
     * @param Customer $customer
     * @return string Customer ID
     * @throws PaymentException
     */
    public function createCustomer(Customer $customer): string
    {
        try {
            // PayPal does not have direct customer creation; use vault for card storage
            throw new PaymentException('PayPal customer creation not implemented yet');
        } catch (\Exception $e) {
            $this->errorHandler->handle($e);
        }
    }
}