<?php

namespace Casdorio\GatewayPayment\Gateways\AuthorizeNet;

use Casdorio\GatewayPayment\Entities\Payment;
use Casdorio\GatewayPayment\Entities\CardInfo;
use net\authorize\api\contract\v1 as AnetAPI;

class AuthorizeNetMapper
{
    public function createTransactionRequest(?Payment $payment, string $transactionType, ?string $refTransId = null, ?string $authCode = null): AnetAPI\TransactionRequestType
    {
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        
        switch ($transactionType) {
            case "authCaptureTransaction":
            case "authOnlyTransaction":
                // Autorização e captura ou apenas autorização
                $transactionRequestType->setTransactionType($transactionType);
                $transactionRequestType->setAmount($payment->amount);
                $transactionRequestType->setOrder($this->createOrder($payment));
                $transactionRequestType->setPayment($this->createPaymentType($payment->card_info));
                $transactionRequestType->setBillTo($this->createCustomerAddress($payment));
                
                if ($payment->delivery_address) {
                    $transactionRequestType->setShipTo($this->createCustomerAddressShip($payment));
                }
                
                $transactionRequestType->setCustomer($this->createCustomerData($payment));
                break;

            case "refundTransaction":
                // Reembolso de uma transação
                $transactionRequestType->setTransactionType($transactionType);
                $transactionRequestType->setAmount($payment->amount);
                $transactionRequestType->setPayment($this->createPaymentType($payment->card_info));
                $transactionRequestType->setRefTransId($refTransId);
                break;

            case "voidTransaction":
                // Cancelamento de uma transação
                $transactionRequestType->setTransactionType($transactionType);
                $transactionRequestType->setRefTransId($refTransId);
                break;

            case "priorAuthCaptureTransaction":
                // Captura de uma transação autorizada
                $transactionRequestType->setTransactionType($transactionType);
                $transactionRequestType->setRefTransId($refTransId);
                
                if ($payment && $payment->amount) {
                    $transactionRequestType->setAmount($payment->amount);
                }
                break;

            case "captureOnlyTransaction":
                // Captura de transação autorizada externamente
                $transactionRequestType->setTransactionType($transactionType);
                if ($payment) {
                    $transactionRequestType->setPayment($this->createPaymentType($payment->card_info, true));
                    $transactionRequestType->setAmount($payment->amount);
                }
                $transactionRequestType->setAuthCode($authCode);
                break;

            case "updateHeldTransaction":
                // Atualiza transação em espera
                $transactionRequestType->setTransactionType($transactionType);
                $transactionRequestType->setRefTransId($refTransId);
                
                if ($payment && $payment->action) {
                    $heldTransactionRequest = new AnetAPI\HeldTransactionRequestType();
                    $heldTransactionRequest->setAction($payment->action); // "approve" ou "decline"
                    $transactionRequestType->setHeldTransactionRequest($heldTransactionRequest);
                }
                break;

            case "creditBankAccount":
                // Crédito em conta bancária
                $transactionRequestType->setTransactionType($transactionType);
                if ($payment) {
                    $transactionRequestType->setAmount($payment->amount);
                    $transactionRequestType->setPayment($this->createBankAccountPaymentType($payment->bank_account, 'credit'));
                }
                break;

            case "debitBankAccount":
                // Débito em conta bancária
                $transactionRequestType->setTransactionType($transactionType);
                if ($payment) {
                    $transactionRequestType->setAmount($payment->amount);
                    $transactionRequestType->setPayment($this->createBankAccountPaymentType($payment->bank_account, 'debit'));
                }
                break;

            case "chargeTokenized":
                // Cobrança com token de cartão
                $transactionRequestType->setTransactionType("authCaptureTransaction");
                if ($payment) {
                    $transactionRequestType->setAmount($payment->amount);
                    $transactionRequestType->setOrder($this->createOrder($payment));
                    $transactionRequestType->setPayment($this->createTokenizedPayment($payment->token_info));
                    $transactionRequestType->setBillTo($this->createCustomerAddress($payment));
                }
                break;

            case "chargeCustomerProfile":
                // Cobrança de perfil de cliente
                $transactionRequestType->setTransactionType("authCaptureTransaction");
                if ($payment) {
                    $transactionRequestType->setAmount($payment->amount);
                    $transactionRequestType->setProfile($this->createCustomerProfilePayment($payment));
                }
                break;
        }
        
        return $transactionRequestType;
    }
    
    // Outros métodos para criar os objetos necessários
    
    public function createPaymentType(CardInfo $card, bool $includeCode = false): AnetAPI\PaymentType
    {
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($card->card_number);
        $creditCard->setExpirationDate($card->expiration_date);
        
        if ($includeCode && $card->cvv) {
            $creditCard->setCardCode($card->cvv);
        }

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setCreditCard($creditCard);
        return $paymentType;
    }
    
    public function createOrder(Payment $payment): AnetAPI\OrderType
    {
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($payment->invoice_number);
        $order->setDescription($payment->description);
        return $order;
    }
    
    public function createCustomerAddress(Payment $payment): AnetAPI\CustomerAddressType
    {
        $customerAddress = new AnetAPI\CustomerAddressType();
        $customerAddress->setFirstName($payment->first_name);
        $customerAddress->setLastName($payment->last_name);
        
        if (isset($payment->phoneNumber)) {
            $customerAddress->setPhoneNumber($payment->phoneNumber);
        }
        
        if ($payment->billing_address) {
            $customerAddress->setAddress($payment->billing_address->address);
            $customerAddress->setCity($payment->billing_address->city);
            $customerAddress->setZip($payment->billing_address->zip_code);
            $customerAddress->setState($payment->billing_address->state);
            $customerAddress->setCountry($payment->billing_address->country);
        }
        
        return $customerAddress;
    }
    
    public function createCustomerAddressShip(Payment $payment): AnetAPI\CustomerAddressType
    {
        $customerShippingAddress = new AnetAPI\CustomerAddressType();
        $customerShippingAddress->setFirstName($payment->first_name);
        $customerShippingAddress->setLastName($payment->last_name);
        
        if ($payment->delivery_address) {
            $customerShippingAddress->setAddress($payment->delivery_address->address);
            $customerShippingAddress->setCity($payment->delivery_address->city);
            $customerShippingAddress->setZip($payment->delivery_address->zip_code);
            $customerShippingAddress->setState($payment->delivery_address->state);
            $customerShippingAddress->setCountry($payment->delivery_address->country);
        }
        
        return $customerShippingAddress;
    }
    
    public function createCustomerData(Payment $payment): AnetAPI\CustomerDataType
    {
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        
        if ($payment->customer_id) {
            $customerData->setId($payment->customer_id);
        }
        
        if ($payment->email) {
            $customerData->setEmail($payment->email);
        }
        
        return $customerData;
    }
    
    // Método para criar informações de pagamento com token
    public function createTokenizedPayment($tokenInfo): AnetAPI\PaymentType
    {
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($tokenInfo->token);
        $creditCard->setExpirationDate($tokenInfo->expiration_date);
        $creditCard->setIsPaymentToken(true);

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setCreditCard($creditCard);
        return $paymentType;
    }
    
    // Método para criar informações de pagamento com conta bancária
    public function createBankAccountPaymentType($bankAccount, $type): AnetAPI\PaymentType
    {
        $bankAccountType = new AnetAPI\BankAccountType();
        $bankAccountType->setAccountType($bankAccount->account_type); // checking, savings, businessChecking
        $bankAccountType->setRoutingNumber($bankAccount->routing_number);
        $bankAccountType->setAccountNumber($bankAccount->account_number);
        $bankAccountType->setNameOnAccount($bankAccount->name_on_account);
        $bankAccountType->setEcheckType($bankAccount->echeck_type); // CCD, PPD, TEL, WEB, etc.
        
        if (isset($bankAccount->bank_name)) {
            $bankAccountType->setBankName($bankAccount->bank_name);
        }

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setBankAccount($bankAccountType);
        
        return $paymentType;
    }
    
    // Método para criar informações de pagamento com perfil de cliente
    public function createCustomerProfilePayment($payment): AnetAPI\CustomerProfilePaymentType
    {
        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($payment->profile_id);
        
        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($payment->payment_profile_id);
        
        $profileToCharge->setPaymentProfile($paymentProfile);
        
        return $profileToCharge;
    }
}