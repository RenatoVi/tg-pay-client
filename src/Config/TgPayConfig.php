<?php

declare(strict_types=1);

namespace TechGenus\TgPay\Config;

final class TgPayConfig
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly ?string $apiKey = null,
        public readonly ?string $webhookSecret = null,
        public readonly int $timeout = 30,
    ) {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            baseUrl: $config['base_url'] ?? '',
            apiKey: $config['api_key'] ?? null,
            webhookSecret: $config['webhook_secret'] ?? null,
            timeout: (int) ($config['timeout'] ?? 30),
        );
    }

    public function toArray(): array
    {
        return [
            'base_url' => $this->baseUrl,
            'api_key' => $this->apiKey,
            'webhook_secret' => $this->webhookSecret,
            'timeout' => $this->timeout,
        ];
    }
}
