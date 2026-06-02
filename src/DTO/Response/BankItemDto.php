<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

final class BankItemDto
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            code: (string) ($data['code'] ?? ''),
            name: (string) ($data['name'] ?? ''),
        );
    }
}
