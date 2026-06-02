<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

final class BillingPortalResponseDto
{
    public function __construct(
        public readonly string $url,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
        );
    }
}
