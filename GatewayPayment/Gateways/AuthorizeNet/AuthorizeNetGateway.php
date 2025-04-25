<?php

namespace Casdorio\GatewayPayment\Gateways\AuthorizeNet;

use Casdorio\GatewayPayment\Interfaces\PaymentGatewayInterface;
use Casdorio\GatewayPayment\Entities\Payment;
use Casdorio\GatewayPayment\Entities\Gateway;
use Casdorio\GatewayPayment\Entities\CardInfo;
use Casdorio\GatewayPayment\Entities\Item;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class AuthorizeNetGateway implements PaymentGatewayInterface
{
    protected $gateway;

    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    // Método de pagamento
    public function chargeCreditCard(Payment $payment)
    {
        return $this->createTransaction($payment, "authCaptureTransaction");
    }

    // Método para autorização
    public function authorize(Payment $payment)
    {
        return $this->createTransaction($payment, "authOnlyTransaction");
    }

    // Método de reembolso
    public function refund($refTransId, $amount)
    {
        return $this->createTransaction($amount, "refundTransaction", $refTransId);
    }

    // Método de cancelamento
    public function void($transactionId)
    {
        return $this->createTransaction(null, "voidTransaction", $transactionId);
    }

    // Método para capturar fundos
    public function capture($transactionId, $amount)
    {
        return $this->createTransaction($amount, "priorAuthCaptureTransaction", $transactionId);
    }

    // public function getAnAcceptPaymentPage($transactionId, $amount)
    // {
    //     $pay = new Payment(
    //         card_number: null,
    //         expiration_date: null,
    //         cvv: null,
    //         amount: $amount,
    //         invoice_number: 'INV-' . time(),
    //         description: 'Descrição da transação',
    //         first_name: null,
    //         last_name: null,
    //         address: null,
    //         city: null,
    //         zip_code: null,
    //         customer_id: null,
    //         email: null
    //     );
    //     return $this->createTransactionAnAcceptPaymentPage($pay, "hostedPaymentButtonOptions", $transactionId);
    // }

    // Método para obter detalhes da transação
    public function getTransactionDetails($transactionId)
    {
        return $this->createTransaction(null, "getTransactionDetails", $transactionId);
    }

    // Método para criar a transação
    private function createTransaction(Payment $payment = null, string $transactionType, $refTransId = null, $authCode = null)
    {
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->createMerchantAuthentication());
        $request->setRefId('ref' . time());
        $request->setTransactionRequest($this->createTransactionRequest($payment, $transactionType, $refTransId, $authCode));

        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(
            $this->gateway->sandbox ? \net\authorize\api\constants\ANetEnvironment::SANDBOX : \net\authorize\api\constants\ANetEnvironment::PRODUCTION
        );

        return ErrorCodeHandler::handlePaymentResponse($response);
    }

    private function createTransactionAnAcceptPaymentPage(Payment $payment = null, string $transactionType, $refTransId = null)
    {
        $request = new AnetAPI\GetHostedPaymentPageRequest();
        $request->setMerchantAuthentication($this->createMerchantAuthentication());
        $request->setRefId('ref' . time());
        $request->setTransactionRequest($this->createTransactionRequest($payment, $transactionType, $refTransId));

        $setting1 = new AnetAPI\SettingType();
        $setting1->setSettingName("hostedPaymentButtonOptions");
        $setting1->setSettingValue("{\"text\": \"Pay\"}");

        $setting2 = new AnetAPI\SettingType();
        $setting2->setSettingName("hostedPaymentOrderOptions");
        $setting2->setSettingValue("{\"show\": false}");

        $setting3 = new AnetAPI\SettingType();
        $setting3->setSettingName("hostedPaymentReturnOptions");
        $setting3->setSettingValue(
            "{\"url\": \"https://admin.ways.us/payment/receipt\", \"cancelUrl\": \"https://admin.ways.us/payment/cancel\", \"showReceipt\": true}"
        );

        $request->addToHostedPaymentSettings($setting1);
        $request->addToHostedPaymentSettings($setting2);
        $request->addToHostedPaymentSettings($setting3);


        $controller = new AnetController\GetHostedPaymentPageController($request);
        $response = $controller->executeWithApiResponse(
            $this->gateway->sandbox ? \net\authorize\api\constants\ANetEnvironment::SANDBOX : \net\authorize\api\constants\ANetEnvironment::PRODUCTION
        );

        print_r($response);
        die;

        return ErrorCodeHandler::handlePaymentResponse($response);
    }

    // Método para criar a requisição da transação com lógica condicional
    private function createTransactionRequest(Payment $payment = null, string $transactionType, $refTransId = null, $authCode = null): AnetAPI\TransactionRequestType
    {
        $transactionRequestType = new AnetAPI\TransactionRequestType();


        // Configuração específica para cada tipo de transação
        switch ($transactionType) {
            case "authCaptureTransaction":
            case "authOnlyTransaction":
                $transactionRequestType->setTransactionType($transactionType);
                $transactionRequestType->setAmount($payment->amount);
                $transactionRequestType->setOrder($this->createOrder($payment));
                $transactionRequestType->setPayment($this->createPaymentType($payment->card_info));
                $transactionRequestType->setBillTo($this->createCustomerAddress($payment));
                $transactionRequestType->setShipTo($this->createCustomerAddressShip($payment));
                //$transactionRequestType->addToLineItems($this->createLineItem($payment));
                $transactionRequestType->setCustomer($this->createCustomerData($payment));
                break;

            case "refundTransaction":
                $transactionRequestType->setTransactionType($transactionType);
                $transactionRequestType->setAmount($payment->amount);
                $transactionRequestType->setPayment($this->createPaymentType($payment->card_info));
                $transactionRequestType->setRefTransId($refTransId);
                break;

            case "voidTransaction":
                $transactionRequestType->setTransactionType($transactionType);
                $transactionRequestType->setRefTransId($refTransId);
                break;

            case "priorAuthCaptureTransaction":
                $transactionRequestType->setTransactionType($transactionType);
                $transactionRequestType->setRefTransId($refTransId);
                break;

            case "captureOnlyTransaction": //nao esta usando 
                $transactionRequestType->setTransactionType($transactionType);
                $transactionRequestType->setPayment($this->createPaymentType($payment->card_info, true));
                $transactionRequestType->setAmount($payment->amount);
                $transactionRequestType->setAuthCode($authCode);
                break;

            case "hostedPaymentButtonOptions":
                $transactionRequestType->setTransactionType('authCaptureTransaction');
                $transactionRequestType->setAmount($payment->amount);
                break;
        }

        return $transactionRequestType;
    }

    // Método para criar a autenticação do merchant
    private function createMerchantAuthentication(): AnetAPI\MerchantAuthenticationType
    {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($this->gateway->login_id);
        $merchantAuthentication->setTransactionKey($this->gateway->transaction_key);
        return $merchantAuthentication;
    }

    // Método para criar o objeto de pagamento
    private function createPaymentType(CardInfo $cart, $capture = false): AnetAPI\PaymentType
    {
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($cart->card_number);
        $creditCard->setExpirationDate($cart->expiration_date);
        if ($capture) {
            $creditCard->setCardCode($cart->cvv);
        }

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setCreditCard($creditCard);
        return $paymentType;
    }

    // Método para criar o endereço de cobrança
    private function createCustomerAddress(Payment $payment): AnetAPI\CustomerAddressType
    {
        $customerAddress = new AnetAPI\CustomerAddressType();
        $customerAddress->setFirstName($payment->first_name);
        $customerAddress->setLastName($payment->last_name);
        $customerAddress->setPhoneNumber($payment->phoneNumber);
        $customerAddress->setAddress($payment->billing_address->address);
        $customerAddress->setCity($payment->billing_address->city);
        $customerAddress->setZip($payment->billing_address->zip_code);
        $customerAddress->setState($payment->billing_address->state);
        $customerAddress->setCountry($payment->billing_address->country);
        return $customerAddress;
    }

    private function createCustomerAddressShip(Payment $payment): AnetAPI\CustomerAddressType
    {
        $customerShippingAddress = new AnetAPI\CustomerAddressType();
        $customerShippingAddress->setFirstName($payment->first_name);
        $customerShippingAddress->setLastName($payment->last_name);
        $customerShippingAddress->setAddress($payment->delivery_address->address);
        $customerShippingAddress->setCity($payment->delivery_address->city);
        $customerShippingAddress->setZip($payment->delivery_address->zip_code);
        $customerShippingAddress->setState($payment->delivery_address->state);
        $customerShippingAddress->setCountry($payment->delivery_address->country);
        return $customerShippingAddress;
    }

    // Métodos adicionais para criar outros objetos (Order e CustomerData)
    private function createOrder(Payment $payment): AnetAPI\OrderType
    {
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($payment->invoice_number);
        $order->setDescription($payment->description);
        return $order;
    }

    private function createCustomerData(Payment $payment): AnetAPI\CustomerDataType
    {
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($payment->customer_id);
        $customerData->setEmail($payment->email);
        return $customerData;
    }
}