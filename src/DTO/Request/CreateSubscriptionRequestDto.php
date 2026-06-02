<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class CreateSubscriptionRequestDto
{
    public function __construct(
        public readonly string $merchant,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $description,
        public readonly string $cycle,
        public readonly string $nextDueDate,
        public readonly CustomerDto $customer,
        public readonly PaymentMethodDto $paymentMethod,
        public readonly ?string $endDate = null,
        public readonly ?int $maxPayments = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            merchant: $data['merchant'],
            amount: (float) $data['amount'],
            currency: $data['currency'],
            description: $data['description'],
            cycle: $data['cycle'],
            nextDueDate: $data['next_due_date'],
            customer: CustomerDto::fromArray($data['customer']),
            paymentMethod: PaymentMethodDto::fromArray($data['payment_method']),
            endDate: $data['end_date'] ?? null,
            maxPayments: isset($data['max_payments']) ? (int) $data['max_payments'] : null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'merchant' => $this->merchant,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'cycle' => $this->cycle,
            'next_due_date' => $this->nextDueDate,
            'customer' => $this->customer->toArray(),
            'payment_method' => $this->paymentMethod->toArray(),
        ];
        if ($this->endDate !== null) {
            $arr['end_date'] = $this->endDate;
        }
        if ($this->maxPayments !== null) {
            $arr['max_payments'] = $this->maxPayments;
        }
        return $arr;
    }
}
