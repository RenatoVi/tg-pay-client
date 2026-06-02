<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class CreatePaymentRequestDto
{
    public function __construct(
        public readonly string $merchant,
        public readonly string $code,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $description,
        public readonly CustomerDto $customer,
        public readonly PaymentMethodDto $paymentMethod,
        public readonly bool $autoCapture = false,
        public readonly ?array $metadata = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            merchant: $data['merchant'],
            code: $data['code'],
            amount: (float) $data['amount'],
            currency: $data['currency'],
            description: $data['description'],
            customer: CustomerDto::fromArray($data['customer']),
            paymentMethod: PaymentMethodDto::fromArray($data['payment_method']),
            autoCapture: $data['auto_capture'] ?? false,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'merchant' => $this->merchant,
            'code' => $this->code,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'customer' => $this->customer->toArray(),
            'payment_method' => $this->paymentMethod->toArray(),
            'auto_capture' => $this->autoCapture,
        ];
        if ($this->metadata !== null) {
            $arr['metadata'] = $this->metadata;
        }
        return $arr;
    }
}
