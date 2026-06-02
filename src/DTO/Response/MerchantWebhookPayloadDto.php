<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

use TechGenus\TgPay\Webhook\MerchantWebhookType;

final class MerchantWebhookPayloadDto
{
    public function __construct(
        public readonly string $webhookType,
        public readonly string $event,
        public readonly int $subscriptionId,
        public readonly ?array $metadata = null,
        public readonly ?string $startedAt = null,
        public readonly ?string $nextDueDate = null,
        public readonly ?array $item = null,
        public readonly ?float $totalAmount = null,
        public readonly ?string $canceledAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $event = (string) ($data['event'] ?? '');
        $webhookType = (string) ($data['webhook_type'] ?? '');
        if ($webhookType === '' && $event !== '') {
            $webhookType = str_starts_with($event, 'subscription.') ? MerchantWebhookType::SUBSCRIPTION : MerchantWebhookType::PAYMENT;
        }
        return new self(
            webhookType: $webhookType,
            event: $event,
            subscriptionId: (int) ($data['subscription_id'] ?? 0),
            metadata: $data['metadata'] ?? null,
            startedAt: $data['started_at'] ?? null,
            nextDueDate: $data['next_due_date'] ?? null,
            item: $data['item'] ?? null,
            totalAmount: isset($data['total_amount']) ? (float) $data['total_amount'] : null,
            canceledAt: $data['canceled_at'] ?? null,
        );
    }

    public function isSubscription(): bool
    {
        return $this->webhookType === MerchantWebhookType::SUBSCRIPTION;
    }

    public function isPayment(): bool
    {
        return $this->webhookType === MerchantWebhookType::PAYMENT;
    }
}
