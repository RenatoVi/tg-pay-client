<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class CreditCardDto
{
    public function __construct(
        public readonly string $number,
        public readonly string $holderName,
        public readonly string $expirationMonth,
        public readonly string $expirationYear,
        public readonly string $cvv,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            number: $data['number'],
            holderName: $data['holder_name'],
            expirationMonth: $data['expiration_month'],
            expirationYear: $data['expiration_year'],
            cvv: $data['cvv'],
        );
    }

    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'holder_name' => $this->holderName,
            'expiration_month' => $this->expirationMonth,
            'expiration_year' => $this->expirationYear,
            'cvv' => $this->cvv,
        ];
    }
}
