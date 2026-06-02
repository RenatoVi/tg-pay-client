<?php

declare(strict_types=1);

namespace TechGenus\TgPay\Webhook;

final class WebhookEvent
{
    public const PAYMENT_STATUS = 'payment_status';
    public const SUBSCRIPTION_PAYMENT = 'subscription_payment';
    public const PIX_PAYMENT = 'pix_payment';
    public const CHECKOUT_SESSION_COMPLETED = 'checkout_session_completed';

    public function __construct(
        public readonly string $type,
        public readonly array $data,
        public readonly ?string $timestamp = null,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            type: (string) ($payload['event'] ?? $payload['type'] ?? ''),
            data: $payload['data'] ?? $payload,
            timestamp: $payload['timestamp'] ?? null,
        );
    }
}
