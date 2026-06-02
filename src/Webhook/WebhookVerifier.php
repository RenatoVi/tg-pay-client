<?php

declare(strict_types=1);

namespace TechGenus\TgPay\Webhook;

use TechGenus\TgPay\TgPayException;

final class WebhookVerifier
{
    public const SIGNATURE_HEADER = 'X-TgPay-Signature';

    public function __construct(
        private readonly string $webhookSecret,
    ) {
    }

    public function verify(string $rawBody, ?string $signatureHeader): WebhookEvent
    {
        if ($signatureHeader === null || $signatureHeader === '') {
            throw TgPayException::webhookSignatureMissing();
        }

        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $this->webhookSecret);
        if (! hash_equals($expected, $signatureHeader)) {
            throw TgPayException::webhookSignatureInvalid();
        }

        $payload = json_decode($rawBody, true);
        if (! is_array($payload)) {
            throw TgPayException::webhookInvalidPayload();
        }

        return WebhookEvent::fromArray($payload);
    }

    public function buildSignature(string $rawBody): string
    {
        return 'sha256=' . hash_hmac('sha256', $rawBody, $this->webhookSecret);
    }
}
