<?php

namespace App\Libraries\OrderManagerService;

use App\Models\OrdersModel;
use App\Models\OrderPaymentModel;

class OrderStatusUpdater
{
    protected $orderModel;
    protected $orderPaymentModel;

    public function __construct()
    {
        $this->orderModel = new OrdersModel();
        $this->orderPaymentModel = new OrderPaymentModel();
    }

    /**
     * Recalcula os totais financeiros de uma ordem de serviço.
     *
     * @param int $orderId
     * @return float Total recalculado
     */
    public function recalculateOrderTotal(int $orderId): float
    {
        // Buscar o total de pagamentos da ordem
        $totalPayments = $this->orderPaymentModel
            ->where('order_id', $orderId)
            ->selectSum('amount')
            ->get()
            ->getRow()
            ->amount;

        // Atualizar o total da ordem no banco
        $this->orderModel->update($orderId, ['total' => $totalPayments]);

        return $totalPayments;
    }
}