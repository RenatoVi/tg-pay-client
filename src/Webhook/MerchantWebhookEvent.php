<?php

declare(strict_types=1);

namespace TechGenus\TgPay\Webhook;

final class MerchantWebhookEvent
{
    public const SUBSCRIPTION_ACTIVATED = 'subscription.activated';
    public const SUBSCRIPTION_RENEWED = 'subscription.renewed';
    public const SUBSCRIPTION_PAYMENT_FAILED = 'subscription.payment_failed';
    public const SUBSCRIPTION_CANCELED = 'subscription.canceled';
    public const SUBSCRIPTION_UPDATED = 'subscription.updated';
    public const SUBSCRIPTION_ITEM_ADDED = 'subscription.item_added';
    public const SUBSCRIPTION_ITEM_REMOVED = 'subscription.item_removed';
    public const SUBSCRIPTION_ITEM_UPDATED = 'subscription.item_updated';
}
