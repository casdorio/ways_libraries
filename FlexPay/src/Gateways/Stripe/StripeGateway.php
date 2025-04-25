<?php

namespace GatewayPayment\Gateways\Stripe;

use GatewayPayment\Entities\Payment;
use GatewayPayment\Entities\Customer;
use GatewayPayment\Entities\PaymentResponse;
use GatewayPayment\Exceptions\PaymentException;
use GatewayPayment\Interfaces\PaymentGatewayInterface;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeGateway implements PaymentGatewayInterface
{
    private StripeClient $client;
    private StripeMapper $mapper;
    private ErrorCodeHandler $errorHandler;

    public function __construct(array $config)
    {
        $this->client = new StripeClient($config['secret_key']);
        $this->mapper = new StripeMapper();
        $this->errorHandler = new ErrorCodeHandler();
    }

    /**
     *�: Charge a payment.
     *
     * @param Payment $payment
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function charge(Payment $payment): PaymentResponse
    {
        try {
            $chargeData = $this->mapper->mapPaymentToCharge($payment);
            $charge = $this->client->charges->create($chargeData);

            return $this->mapper->mapResponseToPaymentResponse($charge);
        } catch (ApiErrorException $e) {
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
            $refund = $this->client->refunds->create([
                'charge' => $transactionId,
                'amount' => (int) ($amount * 100), // Convert to cents
            ]);

            return $this->mapper->mapResponseToPaymentResponse($refund);
        } catch (ApiErrorException $e) {
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
            $customerData = $this->mapper->mapCustomerToStripe($customer);
            $stripeCustomer = $this->client->customers->create($customerData);

            return $stripeCustomer->id;
        } catch (ApiErrorException $e) {
            $this->errorHandler->handle($e);
        }
    }
}