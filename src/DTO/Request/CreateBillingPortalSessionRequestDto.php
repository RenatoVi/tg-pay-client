<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class CreateBillingPortalSessionRequestDto
{
    public function __construct(
        public readonly ?string $priceId = null,
        public readonly ?string $returnUrl = null,
        public readonly ?string $locale = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            priceId: $data['price_id'] ?? null,
            returnUrl: $data['return_url'] ?? null,
            locale: $data['locale'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'price_id' => $this->priceId,
            'return_url' => $this->returnUrl,
            'locale' => $this->locale,
        ], fn ($v) => $v !== null);
    }
}
