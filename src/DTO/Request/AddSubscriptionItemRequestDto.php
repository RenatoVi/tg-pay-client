<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class AddSubscriptionItemRequestDto
{
    public function __construct(
        public readonly float $amount,
        public readonly string $description,
        public readonly string $currency = 'BRL',
        public readonly ?array $metadata = null,
        public readonly ?string $prorationBehavior = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            amount: (float) $data['amount'],
            description: (string) $data['description'],
            currency: $data['currency'] ?? 'BRL',
            metadata: $data['metadata'] ?? null,
            prorationBehavior: $data['proration_behavior'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'amount' => $this->amount,
            'description' => $this->description,
            'currency' => $this->currency,
        ];
        if ($this->metadata !== null) {
            $arr['metadata'] = $this->metadata;
        }
        if ($this->prorationBehavior !== null) {
            $arr['proration_behavior'] = $this->prorationBehavior;
        }
        return $arr;
    }
}
