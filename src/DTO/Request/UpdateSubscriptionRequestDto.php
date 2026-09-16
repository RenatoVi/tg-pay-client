<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class UpdateSubscriptionRequestDto
{
    public function __construct(
        public readonly ?float $value = null,
        public readonly ?string $description = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            value: isset($data['value']) ? (float) $data['value'] : null,
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'value' => $this->value,
            'description' => $this->description,
        ], fn ($v) => $v !== null);
    }
}
