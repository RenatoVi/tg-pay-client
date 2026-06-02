<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

final class PaymentResponseDto
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $orderCode,
        public readonly ?string $gateway,
        public readonly ?string $paymentMethod,
        public readonly float $amount,
        public readonly string $status,
        public readonly ?string $transactionId,
        public readonly ?int $installments,
        public readonly ?string $errorMessage = null,
        public readonly ?string $paidAt = null,
        public readonly ?array $details = null,
        public readonly ?array $events = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            orderCode: $data['order_code'] ?? null,
            gateway: $data['gateway'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            amount: (float) ($data['amount'] ?? 0),
            status: (string) ($data['status'] ?? ''),
            transactionId: $data['transaction_id'] ?? null,
            installments: isset($data['installments']) ? (int) $data['installments'] : null,
            errorMessage: $data['error_message'] ?? null,
            paidAt: $data['paid_at'] ?? null,
            details: $data['details'] ?? null,
            events: $data['events'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
