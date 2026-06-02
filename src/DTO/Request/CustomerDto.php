<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class CustomerDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $document = null,
        public readonly ?string $phone = null,
        public readonly ?string $dob = null,
        public readonly ?AddressDto $address = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $address = null;
        if (! empty($data['address']) && is_array($data['address'])) {
            $address = AddressDto::fromArray($data['address']);
        }

        return new self(
            name: $data['name'],
            email: $data['email'],
            document: $data['document'] ?? null,
            phone: $data['phone'] ?? null,
            dob: $data['dob'] ?? null,
            address: $address,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'name' => $this->name,
            'email' => $this->email,
        ];
        if ($this->document !== null && $this->document !== '') {
            $arr['document'] = $this->document;
        }
        if ($this->phone !== null && $this->phone !== '') {
            $arr['phone'] = $this->phone;
        }
        if ($this->dob !== null) {
            $arr['dob'] = $this->dob;
        }
        if ($this->address !== null) {
            $arr['address'] = $this->address->toArray();
        }
        return $arr;
    }
}
