<?php

declare(strict_types=1);

namespace TechGenus\TgPay\Webhook;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TechGenus\TgPay\DTO\Response\MerchantWebhookPayloadDto;

final class MerchantWebhookHandler
{
    public static function handle(Request $request, MerchantWebhookListener $listener): JsonResponse
    {
        $payload = MerchantWebhookPayloadDto::fromArray($request->all());

        match ($payload->event) {
            MerchantWebhookEvent::SUBSCRIPTION_ACTIVATED => $listener->onSubscriptionActivated($payload->subscriptionId, $payload->metadata, $payload->startedAt, $payload->nextDueDate),
            MerchantWebhookEvent::SUBSCRIPTION_RENEWED => $listener->onSubscriptionRenewed($payload->subscriptionId, $payload->metadata, $payload->startedAt, $payload->nextDueDate),
            MerchantWebhookEvent::SUBSCRIPTION_PAYMENT_FAILED => $listener->onSubscriptionPaymentFailed($payload->subscriptionId, $payload->metadata, $payload->startedAt, $payload->nextDueDate),
            MerchantWebhookEvent::SUBSCRIPTION_CANCELED => $listener->onSubscriptionCanceled($payload->subscriptionId, $payload->metadata, $payload->startedAt, $payload->nextDueDate),
            MerchantWebhookEvent::SUBSCRIPTION_UPDATED => $listener->onSubscriptionUpdated($payload->subscriptionId, $payload->metadata, $payload->startedAt, $payload->nextDueDate),
            MerchantWebhookEvent::SUBSCRIPTION_ITEM_ADDED => $listener->onSubscriptionItemAdded($payload->subscriptionId, $payload->item ?? [], $payload->totalAmount ?? 0.0, $payload->metadata),
            MerchantWebhookEvent::SUBSCRIPTION_ITEM_REMOVED => $listener->onSubscriptionItemRemoved($payload->subscriptionId, $payload->item ?? [], $payload->totalAmount ?? 0.0, $payload->metadata),
            MerchantWebhookEvent::SUBSCRIPTION_ITEM_UPDATED => $listener->onSubscriptionItemUpdated($payload->subscriptionId, $payload->item ?? [], $payload->totalAmount ?? 0.0, $payload->metadata),
            default => null,
        };

        return response()->json(['ok' => true]);
    }
}
