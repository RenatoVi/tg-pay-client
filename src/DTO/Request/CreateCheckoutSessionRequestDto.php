<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class CreateCheckoutSessionRequestDto
{
    /**
     * @param array<int, array{amount: float, description: string, currency?: string}>|null $items
     */
    public function __construct(
        public readonly float $amount,
        public readonly string $cycle,
        public readonly CustomerDto $customer,
        public readonly string $code,
        public readonly string $successUrl,
        public readonly string $cancelUrl,
        public readonly string $currency = 'BRL',
        public readonly ?string $description = null,
        public readonly ?array $metadata = null,
        public readonly ?array $items = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            amount: (float) ($data['amount'] ?? 0),
            cycle: $data['cycle'],
            customer: CustomerDto::fromArray($data['customer']),
            code: $data['code'],
            successUrl: $data['success_url'],
            cancelUrl: $data['cancel_url'],
            currency: $data['currency'] ?? 'BRL',
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? null,
            items: $data['items'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'amount' => $this->amount,
            'cycle' => $this->cycle,
            'customer' => $this->customer->toArray(),
            'code' => $this->code,
            'success_url' => $this->successUrl,
            'cancel_url' => $this->cancelUrl,
            'currency' => $this->currency,
        ];
        if ($this->description !== null) {
            $arr['description'] = $this->description;
        }
        if ($this->metadata !== null) {
            $arr['metadata'] = $this->metadata;
        }
        if ($this->items !== null) {
            $arr['line_items'] = $this->items;
        }
        return $arr;
    }
}
