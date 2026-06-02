<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

final class HealthResponseDto
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $timestamp = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: (string) ($data['status'] ?? ''),
            timestamp: $data['timestamp'] ?? null,
        );
    }
}
