<?php

namespace Casdorio\GatewayPayment\Gateways\AuthorizeNet;

use Casdorio\GatewayPayment\Core\BaseGateway;
use Casdorio\GatewayPayment\Entities\Payment;
use Casdorio\GatewayPayment\Entities\Gateway;
use Casdorio\GatewayPayment\Entities\PaymentResponse;
use Casdorio\GatewayPayment\Core\PaymentLogger;
use Casdorio\GatewayPayment\Exceptions\PaymentException;
use Casdorio\GatewayPayment\Exceptions\ConnectionException;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class AuthorizeNetGateway extends BaseGateway
{
    private $mapper;
    
    public function __construct(Gateway $gateway, ?PaymentLogger $logger = null)
    {
        parent::__construct($gateway, $logger);
        $this->mapper = new AuthorizeNetMapper();
    }
    
    public function chargeCreditCard(Payment $payment): PaymentResponse
    {
        try {
            $response = $this->processTransaction($payment, "authCaptureTransaction");
            $this->logTransaction('charge', $payment->amount, $response->getTransactionId(), $response->isSuccess());
            return $response;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            throw new PaymentException("Erro ao processar pagamento: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function authorize(Payment $payment): PaymentResponse
    {
        try {
            $response = $this->processTransaction($payment, "authOnlyTransaction");
            $this->logTransaction('authorize', $payment->amount, $response->getTransactionId(), $response->isSuccess());
            return $response;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            throw new PaymentException("Erro ao autorizar pagamento: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function capture(string $transactionId, float $amount): PaymentResponse
    {
        try {
            $payment = new Payment();
            $payment->amount = $amount;
            
            $response = $this->processTransaction($payment, "priorAuthCaptureTransaction", $transactionId);
            $this->logTransaction('capture', $amount, $transactionId, $response->isSuccess());
            return $response;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            throw new PaymentException("Erro ao capturar pagamento: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function void(string $transactionId): PaymentResponse
    {
        try {
            $response = $this->processTransaction(null, "voidTransaction", $transactionId);
            $this->logTransaction('void', 0, $transactionId, $response->isSuccess());
            return $response;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            throw new PaymentException("Erro ao cancelar pagamento: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function refund(string $transactionId, float $amount): PaymentResponse
    {
        try {
            $payment = new Payment();
            $payment->amount = $amount;
            
            $response = $this->processTransaction($payment, "refundTransaction", $transactionId);
            $this->logTransaction('refund', $amount, $transactionId, $response->isSuccess());
            return $response;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            throw new PaymentException("Erro ao reembolsar pagamento: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function getTransactionDetails(string $transactionId): PaymentResponse
    {
        try {
            // Implementação específica para obter detalhes da transação
            $request = new AnetAPI\GetTransactionDetailsRequest();
            $request->setMerchantAuthentication($this->createMerchantAuthentication());
            $request->setTransId($transactionId);
            
            $controller = new AnetController\GetTransactionDetailsController($request);
            $response = $controller->executeWithApiResponse(
                $this->gateway->sandbox ? 
                \net\authorize\api\constants\ANetEnvironment::SANDBOX : 
                \net\authorize\api\constants\ANetEnvironment::PRODUCTION
            );
            
            return ErrorCodeHandler::handleDetailsResponse($response);
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            throw new PaymentException("Erro ao obter detalhes da transação: " . $e->getMessage(), 0, $e);
        }
    }
    
    // Métodos privados auxiliares
    
    private function processTransaction(?Payment $payment, string $transactionType, string $refTransId = null): PaymentResponse
    {
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->createMerchantAuthentication());
        $request->setRefId('ref' . time());
        
        // Cria a requisição de transação com base no tipo
        $transactionRequest = $this->mapper->createTransactionRequest($payment, $transactionType, $refTransId);
        $request->setTransactionRequest($transactionRequest);
        
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(
            $this->gateway->sandbox ? 
            \net\authorize\api\constants\ANetEnvironment::SANDBOX : 
            \net\authorize\api\constants\ANetEnvironment::PRODUCTION
        );
        
        // Trata a resposta usando o ErrorCodeHandler
        return ErrorCodeHandler::handlePaymentResponse($response);
    }
    
    private function createMerchantAuthentication(): AnetAPI\MerchantAuthenticationType
    {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($this->gateway->login_id);
        $merchantAuthentication->setTransactionKey($this->gateway->transaction_key);
        return $merchantAuthentication;
    }
}