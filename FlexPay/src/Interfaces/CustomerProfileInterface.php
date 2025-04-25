<?php

namespace GatewayPayment\Interfaces;

interface CustomerProfileInterface
{
    /**
     * Cria um perfil de cliente no gateway
     * 
     * @param array $customerData Dados do cliente
     * @return \GatewayPayment\Entities\PaymentResponse
     */
    public function createCustomerProfile(array $customerData);

    /**
     * Adiciona um método de pagamento ao perfil do cliente
     * 
     * @param string $customerProfileId ID do perfil do cliente
     * @param array $paymentData Dados do método de pagamento
     * @return \GatewayPayment\Entities\PaymentResponse
     */
    public function addPaymentMethod(string $customerProfileId, array $paymentData);

    /**
     * Remove um método de pagamento do perfil do cliente
     * 
     * @param string $customerProfileId ID do perfil do cliente
     * @param string $paymentMethodId ID do método de pagamento
     * @return \GatewayPayment\Entities\PaymentResponse
     */
    public function removePaymentMethod(string $customerProfileId, string $paymentMethodId);

    /**
     * Atualiza um perfil de cliente
     * 
     * @param string $customerProfileId ID do perfil do cliente
     * @param array $customerData Dados do cliente
     * @return \GatewayPayment\Entities\PaymentResponse
     */
    public function updateCustomerProfile(string $customerProfileId, array $customerData);

    /**
     * Cobra um cliente usando um método de pagamento salvo
     * 
     * @param string $customerProfileId ID do perfil do cliente
     * @param string $paymentMethodId ID do método de pagamento
     * @param float $amount Valor a ser cobrado
     * @param array $additionalData Dados adicionais para a transação
     * @return \GatewayPayment\Entities\PaymentResponse
     */
    public function chargeCustomer(string $customerProfileId, string $paymentMethodId, float $amount, array $additionalData = []);
}