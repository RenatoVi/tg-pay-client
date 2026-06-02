<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

final class SubscriptionItemResponseDto
{
    public function __construct(
        public readonly int $id,
        public readonly int $subscriptionId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $description,
        public readonly string $status,
        public readonly ?array $metadata = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            subscriptionId: (int) ($data['subscription_id'] ?? 0),
            amount: (float) ($data['amount'] ?? 0),
            currency: (string) ($data['currency'] ?? 'BRL'),
            description: (string) ($data['description'] ?? ''),
            status: (string) ($data['status'] ?? 'active'),
            metadata: $data['metadata'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
