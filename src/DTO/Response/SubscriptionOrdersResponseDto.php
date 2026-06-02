<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

final class SubscriptionOrdersResponseDto
{
    /** @var array<int, array{order_code: string, billing_date: ?string, amount: float, status: string, charges: array}> */
    public readonly array $orders;

    public function __construct(
        public readonly string $subscriptionId,
        array $orders = [],
    ) {
        $this->orders = $orders;
    }

    public static function fromArray(array $data): self
    {
        $orders = $data['orders'] ?? [];
        if (isset($data['subscription_id'])) {
            return new self((string) $data['subscription_id'], $orders);
        }
        return new self('', $orders);
    }
}
