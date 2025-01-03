<?php

namespace Casdorio\GatewayPayment\Controllers;

use CodeIgniter\Controller;
use Casdorio\GatewayPayment\Services\PaymentGatewayFactory;
use Casdorio\GatewayPayment\Services\PaymentService;
use Casdorio\GatewayPayment\Entities\Payment;
use Casdorio\GatewayPayment\Entities\Gateway;
use Exception;

class PaymentController extends Controller
{
    private static function initializePaymentService(array $gatewayConfig)
    {
        if (!$gatewayConfig) {
            return [
                'status' => 'error',
                'code' => 'CONFIG_NOT_FOUND',
                'gatewayName' => '',
                'message' => 'No payment gateway configuration found for this office.'
            ];
        }

        $gatewayEntity = new Gateway(
            id: null,
            name: $gatewayConfig['gateway_name'],
            login_id: $gatewayConfig['login_id'],
            transaction_key: $gatewayConfig['transaction_key'],
            sandbox: $gatewayConfig['environment'] === 'sandbox',
        );

        try {
            $gateway = PaymentGatewayFactory::create($gatewayEntity);
            return new PaymentService($gateway);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'code' => 'GATEWAY_CREATION_FAILED',
                'gatewayName' => $gatewayConfig['gateway_name'],
                'message' => 'Transaction failed. ' . $e->getMessage()
            ];
        }
    }

    public static function chargeCreditCard(array $gatewayConfig, Payment $payment): array
    {
        $paymentService = self::initializePaymentService($gatewayConfig);

        if (is_array($paymentService)) {
            return $paymentService;
        }

        return $paymentService->chargeCreditCard($payment);
    }

    public static function authorize(array $gatewayConfig, Payment $payment): array
    {
        $paymentService = self::initializePaymentService($gatewayConfig);

        if (is_array($paymentService)) {
            return $paymentService;
        }

        return $paymentService->authorize($payment);
    }
    // captureFunds  priorAuthCaptureTransaction

    public static function refund(array $gatewayConfig, Payment $payment, $transactionId)
    {
        $paymentService = self::initializePaymentService($gatewayConfig);

        if (is_array($paymentService)) {
            return $paymentService;
        }

        return $paymentService->refund($payment, $transactionId);
    }

    public static function voidTransaction(array $gatewayConfig, $transactionId)
    {
        $paymentService = self::initializePaymentService($gatewayConfig);

        if (is_array($paymentService)) {
            return $paymentService;
        }

        return $paymentService->void($transactionId);
    }

    public static function captureTransaction(array $gatewayConfig, $transactionId, $amount)
    {
        $paymentService = self::initializePaymentService($gatewayConfig);

        if (is_array($paymentService)) {
            return $paymentService;
        }

        return $paymentService->capture($transactionId, $amount);
    }

    public static function getAnAcceptPaymentPage(array $gatewayConfig, $transactionId, $amount)
    {
        $paymentService = self::initializePaymentService($gatewayConfig);

        if (is_array($paymentService)) {
            return $paymentService;
        }

        return $paymentService->getAnAcceptPaymentPage($transactionId, $amount);
    }

    //getTransactionDetails
    public static function getTransactionDetails(array $gatewayConfig, $transactionId)
    {
        $paymentService = self::initializePaymentService($gatewayConfig);

        if (is_array($paymentService)) {
            return $paymentService;
        }

        return $paymentService->getTransactionDetails($transactionId);
    }
}