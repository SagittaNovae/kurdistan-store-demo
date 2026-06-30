<?php

namespace Store\KurdistanStore\Services\Payment\Gateways;

use Store\KurdistanStore\Models\PaymentTransaction;

class CashOnDeliveryGateway
{
    public function __construct(protected array $config = []) {}

    public function getCode(): string
    {
        return 'cashondelivery';
    }

    public function initiate(array $orderData): array
    {
        $transaction = PaymentTransaction::create([
            'order_id' => $orderData['order_id'] ?? null,
            'customer_id' => $orderData['customer_id'] ?? null,
            'gateway' => $this->getCode(),
            'reference' => 'COD-'.($orderData['order_id'] ?? uniqid()),
            'amount' => $orderData['amount'],
            'currency' => $orderData['currency'] ?? 'IQD',
            'status' => 'pending',
            'payload' => $orderData,
        ]);

        return [
            'success' => true,
            'gateway' => $this->getCode(),
            'reference' => $transaction->reference,
            'status' => 'pending',
            'message' => 'Order placed. Pay on delivery.',
        ];
    }

    public function verify(string $reference, array $payload = []): array
    {
        return ['success' => true, 'status' => 'pending'];
    }
}
