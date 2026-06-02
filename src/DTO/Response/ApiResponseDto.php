<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

final class ApiResponseDto
{
    public function __construct(
        public readonly bool $success,
        public readonly ?array $data = null,
        public readonly ?string $message = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'] ?? false,
            data: $data['data'] ?? null,
            message: $data['message'] ?? null,
        );
    }
}
