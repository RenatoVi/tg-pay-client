<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class AddressDto
{
    public function __construct(
        public readonly ?string $street = null,
        public readonly ?string $number = null,
        public readonly ?string $neighborhood = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $country = null,
        public readonly ?string $zipCode = null,
        public readonly ?string $complement = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'] ?? null,
            number: $data['number'] ?? null,
            neighborhood: $data['neighborhood'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            country: $data['country'] ?? null,
            zipCode: $data['zip_code'] ?? null,
            complement: $data['complement'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [];
        if ($this->street !== null) {
            $arr['street'] = $this->street;
        }
        if ($this->number !== null) {
            $arr['number'] = $this->number;
        }
        if ($this->neighborhood !== null) {
            $arr['neighborhood'] = $this->neighborhood;
        }
        if ($this->city !== null) {
            $arr['city'] = $this->city;
        }
        if ($this->state !== null) {
            $arr['state'] = $this->state;
        }
        if ($this->country !== null) {
            $arr['country'] = $this->country;
        }
        if ($this->zipCode !== null) {
            $arr['zip_code'] = $this->zipCode;
        }
        if ($this->complement !== null) {
            $arr['complement'] = $this->complement;
        }
        return $arr;
    }
}
