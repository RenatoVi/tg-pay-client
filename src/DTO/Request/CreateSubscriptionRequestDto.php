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
        public readonly CustomerDto $customer,
        public readonly PaymentMethodDto $paymentMethod,
        /**
         * Vencimento da primeira cobrança (Y-m-d).
         *
         * Nulo deixa o gateway decidir — em boleto e pix o vencimento é do
         * gateway, e forçar uma data aqui só acerta por acaso.
         */
        public readonly ?string $nextDueDate = null,
        public readonly ?string $endDate = null,
        public readonly ?int $maxPayments = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $metadata = null,
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
            customer: CustomerDto::fromArray($data['customer']),
            paymentMethod: PaymentMethodDto::fromArray($data['payment_method']),
            nextDueDate: $data['next_due_date'] ?? null,
            endDate: $data['end_date'] ?? null,
            maxPayments: isset($data['max_payments']) ? (int) $data['max_payments'] : null,
            metadata: $data['metadata'] ?? null,
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
            'customer' => $this->customer->toArray(),
            'payment_method' => $this->paymentMethod->toArray(),
        ];
        // Ausente, o gateway decide o vencimento — que é o certo em boleto e pix.
        if ($this->nextDueDate !== null) {
            $arr['next_due_date'] = $this->nextDueDate;
        }
        if ($this->metadata !== null) {
            $arr['metadata'] = $this->metadata;
        }
        if ($this->endDate !== null) {
            $arr['end_date'] = $this->endDate;
        }
        if ($this->maxPayments !== null) {
            $arr['max_payments'] = $this->maxPayments;
        }
        return $arr;
    }
}
