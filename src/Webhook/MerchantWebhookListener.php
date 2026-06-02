<?php

declare(strict_types=1);

namespace TechGenus\TgPay\Webhook;

abstract class MerchantWebhookListener
{
    public function onSubscriptionActivated(int $subscriptionId, ?array $metadata = null, ?string $startedAt = null, ?string $nextDueDate = null): void {}

    public function onSubscriptionRenewed(int $subscriptionId, ?array $metadata = null, ?string $startedAt = null, ?string $nextDueDate = null): void {}

    public function onSubscriptionPaymentFailed(int $subscriptionId, ?array $metadata = null, ?string $startedAt = null, ?string $nextDueDate = null): void {}

    public function onSubscriptionCanceled(int $subscriptionId, ?array $metadata = null, ?string $startedAt = null, ?string $nextDueDate = null): void {}

    public function onSubscriptionUpdated(int $subscriptionId, ?array $metadata = null, ?string $startedAt = null, ?string $nextDueDate = null): void {}

    public function onSubscriptionItemAdded(int $subscriptionId, array $item, float $totalAmount, ?array $metadata = null): void {}

    public function onSubscriptionItemRemoved(int $subscriptionId, array $item, float $totalAmount, ?array $metadata = null): void {}

    public function onSubscriptionItemUpdated(int $subscriptionId, array $item, float $totalAmount, ?array $metadata = null): void {}
}
