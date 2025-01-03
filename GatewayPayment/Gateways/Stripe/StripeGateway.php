<?php

namespace Casdorio\GatewayPayment\Gateways\Stripe;

use Casdorio\GatewayPayment\Entities\Gateway;
use Casdorio\GatewayPayment\Interfaces\PaymentGatewayInterface;
use Casdorio\GatewayPayment\Entities\Payment;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        \Stripe\Stripe::setApiKey(getenv('stripe_secret_key'));
    }

    public function chargeCreditCard(Payment $payment)
    {
        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'payment_method' => $payment->payment_method,
                'confirm' => true,
            ]);

            $payment->status = 'succeeded';
            $payment->transaction_id = $paymentIntent->id;
        } catch (\Exception $e) {
            $payment->status = 'failed';
            $payment->error_message = $e->getMessage();
        }

        return $payment;
    }

    public function authorize(Payment $payment)
    {
        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'payment_method' => $payment->payment_method,
                'capture_method' => 'manual', // Define que será apenas autorizado
            ]);

            $payment->status = 'authorized';
            $payment->transaction_id = $paymentIntent->id;
        } catch (\Exception $e) {
            $payment->status = 'failed';
            $payment->error_message = $e->getMessage();
        }

        return $payment;
    }

    public function refund($transactionId)
    {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $transactionId,
            ]);
            return ['status' => 'refunded', 'refund_id' => $refund->id];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'error_message' => $e->getMessage()];
        }
    }

    public function void($transactionId)
    {
        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($transactionId);
            $paymentIntent->cancel();
            return ['status' => 'voided'];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'error_message' => $e->getMessage()];
        }
    }
}