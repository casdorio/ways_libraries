<?php

namespace App\Libraries\OrderManagerService;

use App\Helpers\StatusHelper;

class OrderCalculatorUpdater
{
    protected $orderModel;
    protected $orderPaymentModel;
    protected $orderProductModel;

    public function __construct()
    {
        $this->orderModel = model('OrdersModel');
        $this->orderPaymentModel = model('OrderPaymentModel');
        $this->orderProductModel = model('OrderProductModel');
    }

    public function recalculateOrderTotals(array $orderIds): void
    {
        try {
            if (empty($orderIds)) {
                throw new \InvalidArgumentException("Order with ID not found.");
            }

            foreach ($orderIds as $orderId) {
                $this->recalculateOrder($orderId);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function recalculateOrder(string $orderId): bool
    {
        // Verifica se o pedido existe
        $order = $this->orderModel->find($orderId);

        if ($order === null) {
            throw new \InvalidArgumentException("Order with ID '{$orderId}' not found.");
        }

        // Inicializa os valores do pedido
        $order['amount'] = 0.0;
        $order['materialCost'] = 0.0;
        $order['deliveryCost'] = 0.0;
        $order['deliveryCostExtra'] = 0.0;
        $order['taxCharge'] = 0.0;
        $order['discount'] = 0.0;
        $order['taxRate'] = 0.0;
        $order['salesTax'] = 0.0;
        $order['totalPaid'] = 0.0;
        $order['totalDue'] = 0.0;
        $order['totalWaiting'] = 0.0;

        // Verifica se existem produtos no pedido
        $orderProducts = $this->orderProductModel->where('order_id', $order['id'])->findAll();


        foreach ($orderProducts as $orderProduct) {
            // Verifica se o produto tem todos os campos necessários
            if (!isset($orderProduct['deliveryCost'], $orderProduct['deliveryCostExtra'], $orderProduct['taxCharge'], $orderProduct['salesTax'], $orderProduct['materialCost'], $orderProduct['discount'], $orderProduct['taxRate'])) {
                throw new \UnexpectedValueException("Order product data is missing required fields.");
            }

            // Cálculo dos custos e taxas
            $deliveryCostFull = $orderProduct['deliveryCost'] + $orderProduct['deliveryCostExtra'];
            $taxs = $orderProduct['taxCharge'] + $orderProduct['salesTax'];

            $order['amount'] += $deliveryCostFull + $taxs + $orderProduct['materialCost'] - $orderProduct['discount'];
            $order['materialCost'] += $orderProduct['materialCost'];
            $order['deliveryCost'] += $orderProduct['deliveryCost'];
            $order['deliveryCostExtra'] += $orderProduct['deliveryCostExtra'];
            $order['taxCharge'] += $orderProduct['taxCharge'];
            $order['discount'] += $orderProduct['discount'];
            $order['taxRate'] = $orderProduct['taxRate'];
            $order['salesTax'] += $orderProduct['salesTax'];
        }

        // Recupera o histórico de transações
        $transactions = $this->orderPaymentModel->select(['status', 'amount'])->where('order_id', $order['id'])->findAll();

        $this->calculatePayments($order, $transactions);

        $order['paymentStatus'] = $this->determinePaymentStatus($order, $transactions);
        $order['orderStatus'] = $this->determineOrderStatus($order);
        unset($order['created_at'], $order['updated_at'], $order['deleted_at']);

        // Salva as atualizações do pedido
        if (!$this->orderModel->save($order)) {
            throw new \RuntimeException("Failed to save order with ID '{$orderId}'.");
        }
        return true;
    }

    private function calculatePayments(array &$order, array $transactions): void
    {
        $totalPaid = 0.0;
        $totalWaiting = 0.0;

        foreach ($transactions as $transaction) {
            // Verifica se o campo status é válido
            if (!isset($transaction['status']) || !is_numeric($transaction['status'])) {
                throw new \UnexpectedValueException("Invalid transaction status for order ID '{$order['id']}'.");
            }

            if (in_array(
                $transaction['status'],
                [StatusHelper::getTransactionPaymentStatusId('PAID'), StatusHelper::getTransactionPaymentStatusId('CAPTURED')]
            )) {
                $totalPaid += (float) $transaction['amount'];
            }

            if (in_array(
                $transaction['status'],
                [StatusHelper::getTransactionPaymentStatusId('AUTHORIZED'), StatusHelper::getTransactionPaymentStatusId('PENDING')]
            )) {
                $totalWaiting += (float) $transaction['amount'];
            }
        }

        $order['totalPaid'] = $totalPaid;
        $order['totalDue'] = $order['amount'] - $totalPaid;
        $order['totalWaiting'] = $totalWaiting;
    }

    private function determinePaymentStatus(array $order, array $transactions): int
    {
        $TOTAL_DUE = $order['totalDue'];
        return $this->determineOrderPaymentStatus($transactions, $TOTAL_DUE);
        // //status 0 ou Not Paid
        // // Se o total pago é 0 e o total devido é maior que 0, não pago
        // if ($TOTAL_PAID == 0 && $TOTAL_DUE > 0) {
        //     return StatusHelper::getPaymentStatusId('NOT_PAID'); // Not Paid
        // }

        // //status 1 ou Paid
        // if ($TOTAL_PAID == 0 && $TOTAL_PAID == $TOTAL_DUE > 0) {
        //     return StatusHelper::getPaymentStatusId('PAID'); // Paid
        // }

        // //status 2 ou FAILED

        // // Se o total pago é igual ao total devido e o total devido é maior que 0, está pago parcialmente
        // if ($TOTAL_PAID == $TOTAL_DUE && $TOTAL_DUE > 0) {
        //     return StatusHelper::getPaymentStatusId('PARTIALLY_PAID'); // Paid
        // }

        // // Se o total pago e o total devido são 0, o pagamento está totalmente realizado
        // if ($TOTAL_PAID == 0 && $TOTAL_DUE == 0) {
        //     return StatusHelper::getPaymentStatusId('PAID'); // Fully Paid
        // }

        // // Se o total pago for maior que 0 e o total devido também for maior que 0, pagamento parcial
        // if ($TOTAL_PAID > 0 && $TOTAL_DUE > 0) {
        //     return StatusHelper::getPaymentStatusId(StatusHelper::ORDER_PAYMENT_STATUS[4]['param']); // Partially Paid
        // }

        // // Se o total pago for maior que 0 e o total devido for 0, pago
        // if ($TOTAL_PAID > 0 && $TOTAL_DUE == 0) {
        //     return StatusHelper::getPaymentStatusId(StatusHelper::ORDER_PAYMENT_STATUS[1]['param']); // Paid
        // }



        // // Se o total devido for negativo, reembolsado
        // if ($TOTAL_DUE < 0) {
        //     return StatusHelper::getPaymentStatusId(StatusHelper::ORDER_PAYMENT_STATUS[7]['param']); // Refunded
        // }

        // // Se não corresponder a nenhum dos casos acima, então pendente
        // return StatusHelper::getPaymentStatusId(StatusHelper::ORDER_PAYMENT_STATUS[5]['param']); // Pending Payment

    }

    private function determineOrderStatus(array $order): int
    {
        $deliveries = model('OrderDeliveryModel')->getDeliveriesByOrderId($order['id']);

        //se nao tem delivery entao status é 0
        if (empty($deliveries)) {
            return 0;
        }

        // Inicializa status de entrega
        $deliveryStatuses = array_column($deliveries, 'deliveryStatus');

        // Verifica se há algum status de entrega cancelado
        if (in_array(4, $deliveryStatuses)) {
            return 3; // Pedido cancelado
        }

        // Verifica se todos os deliveries estão completos
        if (array_filter($deliveryStatuses, function ($status) {
            return $status !== 3;
        }) === []) {
            // Todos os deliveryStatus são 'Completed'
            if ($order['paymentStatus'] === 1) { // Payment status is 'Paid'
                return 2; // Pedido concluído
            }
            return 1; // Pedido em processamento
        }

        // Verifica se há algum status de entrega em trânsito ou agendado
        if (in_array(2, $deliveryStatuses) || in_array(1, $deliveryStatuses)) {
            return 1; // Pedido em processamento
        }

        return 0;
    }

    private function determineOrderPaymentStatus(array $transactions, $TOTAL_DUE): int
    {
        $transactionStatuses = array_column($transactions, 'status');

        //remover alguns status varios status
        // $transactionStatuses = array_filter($transactionStatuses, function ($status) {
        //     return in_array($status, [0, 5]);
        // });

        // print_r($transactionStatuses);
        // die;

        // Contagem dos diferentes status
        $hasPending = in_array(0, $transactionStatuses); // Pending
        $hasPaid = in_array(1, $transactionStatuses); // Paid
        $hasFailed = in_array(2, $transactionStatuses); // Failed
        $hasAuthCancelled = in_array(3, $transactionStatuses); // Authorization Cancelled
        $hasRefunded = in_array(4, $transactionStatuses); // Refunded
        $hasCancelled = in_array(5, $transactionStatuses); // Cancelled
        $hasAuthorized = in_array(6, $transactionStatuses); // Authorized
        $hasCaptured = in_array(7, $transactionStatuses); // Captured

        $countPaid = count(array_filter($transactionStatuses, function ($status) {
            return $status == 1; // Verificando valores diretamente
        }));

        $countAuthorized = count(array_filter($transactionStatuses, function ($status) {
            return $status == 6; // Verificando valores diretamente
        }));

        $countRefunded = count(array_filter($transactionStatuses, function ($status) {
            return $status == 4; // Verificando valores diretamente
        }));

        // Determinar o status da ordem com base nos status das transações
        if ($hasRefunded && $countRefunded === count($transactionStatuses) && $TOTAL_DUE > 0) {
            return 5; // Refunded
        }

        if ($hasRefunded && $TOTAL_DUE > 0) {
            if ($hasPaid) {
                return 2; // Partially Paid
            }
            return 6; // Partially Refunded
        }

        if ($hasPaid && $TOTAL_DUE <= 0) {
            return 1; // Paid
        }

        if ($hasPaid && $TOTAL_DUE > 0) {
            return 2; // Partially Paid
        }

        if ($hasAuthorized && $countAuthorized === count($transactionStatuses) && $TOTAL_DUE <= 0) {
            return 3; // Authorized
        }

        if ($hasAuthorized && $TOTAL_DUE > 0) {
            return 4; // Partially Authorized
        }

        if ($TOTAL_DUE < 0) {
            return 7; // Partially Authorized
        }

        if ($hasPending && $TOTAL_DUE >= 0) {
            return 0; // Not Paid
        }

        return 0; // Padrão: Not Paid
    }
}