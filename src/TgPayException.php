<?php

declare(strict_types=1);

namespace TechGenus\TgPay;

use RuntimeException;

class TgPayException extends RuntimeException
{
    public static function webhookSignatureMissing(): self
    {
        return new self('Webhook signature header is missing');
    }

    public static function webhookSignatureInvalid(): self
    {
        return new self('Webhook signature is invalid');
    }

    public static function webhookInvalidPayload(): self
    {
        return new self('Webhook payload is invalid');
    }
}
