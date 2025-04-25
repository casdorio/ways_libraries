<?php

namespace Ways\GatewayPayment\Entities;

class Payment
{
    /**
     * ID da transação
     * 
     * @var string|null
     */
    private $transactionId;
    
    /**
     * Valor da transação
     * 
     * @var float
     */
    private $amount;
    
    /**
     * Moeda da transação
     * 
     * @var string
     */
    private $currency;
    
    /**
     * Descrição da transação
     * 
     * @var string|null
     */
    private $description;
    
    /**
     * Referência da fatura
     * 
     * @var string|null
     */
    private $invoiceNumber;
    
    /**
     * Informações do cartão
     * 
     * @var \Ways\GatewayPayment\Entities\CardInfo|null
     */
    private $cardInfo;
    
    /**
     * Informações da conta bancária
     * 
     * @var \Ways\GatewayPayment\Entities\BankAccount|null
     */
    private $bankAccount;
    
    /**
     * Informações do cliente
     * 
     * @var \Ways\GatewayPayment\Entities\Customer|null
     */
    private $customer;
    
    /**
     * Itens da transação
     * 
     * @var array
     */
    private $items = [];
    
    /**
     * Dados adicionais da transação
     * 
     * @var array
     */
    private $metadata = [];

    /**
     * Construtor
     * 
     * @param float $amount Valor da transação
     * @param string $currency Moeda da transação
     */
    public function __construct(float $amount, string $currency = 'USD')
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }
    
    /**
     * Cria uma instância a partir de um array
     * 
     * @param array $data Dados da transação
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $payment = new self(
            $data['amount'] ?? 0,
            $data['currency'] ?? 'USD'
        );
        
        if (isset($data['transaction_id'])) {
            $payment->setTransactionId($data['transaction_id']);
        }
        
        if (isset($data['description'])) {
            $payment->setDescription($data['description']);
        }
        
        if (isset($data['invoice_number'])) {
            $payment->setInvoiceNumber($data['invoice_number']);
        }
        
        if (isset($data['card'])) {
            $payment->setCardInfo(CardInfo::fromArray($data['card']));
        }
        
        if (isset($data['bank_account'])) {
            $payment->setBankAccount(BankAccount::fromArray($data['bank_account']));
        }
        
        if (isset($data['customer'])) {
            $payment->setCustomer(Customer::fromArray($data['customer']));
        }
        
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $payment->addItem(Item::fromArray($itemData));
            }
        }
        
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $payment->setMetadata($data['metadata']);
        }
        
        return $payment;
    }
    
    /**
     * Converte a instância para um array
     * 
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'amount' => $this->amount,
            'currency' => $this->currency
        ];
        
        if ($this->transactionId) {
            $data['transaction_id'] = $this->transactionId;
        }
        
        if ($this->description) {
            $data['description'] = $this->description;
        }
        
        if ($this->invoiceNumber) {
            $data['invoice_number'] = $this->invoiceNumber;
        }
        
        if ($this->cardInfo) {
            $data['card'] = $this->cardInfo->toArray();
        }
        
        if ($this->bankAccount) {
            $data['bank_account'] = $this->bankAccount->toArray();
        }
        
        if ($this->customer) {
            $data['customer'] = $this->customer->toArray();
        }
        
        if (!empty($this->items)) {
            $data['items'] = array_map(function (Item $item) {
                return $item->toArray();
            }, $this->items);
        }
        
        if (!empty($this->metadata)) {
            $data['metadata'] = $this->metadata;
        }
        
        return $data;
    }

    // Getters e Setters

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): self
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getCardInfo(): ?CardInfo
    {
        return $this->cardInfo;
    }

    public function setCardInfo(?CardInfo $cardInfo): self
    {
        $this->cardInfo = $cardInfo;
        return $this;
    }

    public function getBankAccount(): ?BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccount $bankAccount): self
    {
        $this->bankAccount = $bankAccount;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    public function addItem(Item $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function addMetadata(string $key, $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }
}