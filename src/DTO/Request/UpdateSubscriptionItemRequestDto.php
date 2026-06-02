<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class UpdateSubscriptionItemRequestDto
{
    public function __construct(
        public readonly ?float $amount = null,
        public readonly ?string $description = null,
        public readonly ?string $currency = null,
        public readonly ?array $metadata = null,
        public readonly ?string $prorationBehavior = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            description: $data['description'] ?? null,
            currency: $data['currency'] ?? null,
            metadata: $data['metadata'] ?? null,
            prorationBehavior: $data['proration_behavior'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'description' => $this->description,
            'currency' => $this->currency,
            'metadata' => $this->metadata,
            'proration_behavior' => $this->prorationBehavior,
        ], fn ($v) => $v !== null);
    }
}
