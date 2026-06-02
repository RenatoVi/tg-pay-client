<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

final class SubscriptionResponseDto
{
    public function __construct(
        public readonly string $subscriptionId,
        public readonly string $status,
        public readonly ?string $cycle = null,
        public readonly ?string $startedAt = null,
        public readonly ?string $canceledAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            subscriptionId: (string) ($data['subscription_id'] ?? $data['id'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            cycle: $data['cycle'] ?? null,
            startedAt: $data['started_at'] ?? null,
            canceledAt: $data['canceled_at'] ?? null,
        );
    }
}
