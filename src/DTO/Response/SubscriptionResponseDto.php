<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

final class SubscriptionResponseDto
{
    public function __construct(
        public readonly string $subscriptionId,
        public readonly string $status,
        public readonly ?string $cycle = null,
        public readonly ?string $startedAt = null,
        public readonly ?string $canceledAt = null,
        /**
         * Cobrança do PRIMEIRO ciclo, em boleto e pix. Nula em cartão.
         *
         * Vence: a partir do segundo ciclo o gateway emite outra, e a atual sai
         * de Client::getSubscriptionCharge().
         */
        public readonly ?ChargeDto $charge = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            subscriptionId: (string) ($data['subscription_id'] ?? $data['id'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            cycle: $data['cycle'] ?? null,
            startedAt: $data['started_at'] ?? null,
            canceledAt: $data['canceled_at'] ?? null,
            charge: ! empty($data['charge']) && is_array($data['charge'])
                ? ChargeDto::fromArray($data['charge'])
                : null,
        );
    }
}
