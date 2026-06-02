<?php

declare(strict_types=1);

namespace TechGenus\TgPay\Facade;

use Illuminate\Support\Facades\Facade;
use TechGenus\TgPay\Client;

/**
 * @method static \TechGenus\TgPay\DTO\Response\HealthResponseDto health()
 * @method static \TechGenus\TgPay\DTO\Response\BankItemDto[] getBanks()
 * @method static \TechGenus\TgPay\DTO\Response\PaymentResponseDto createPayment(\TechGenus\TgPay\DTO\Request\CreatePaymentRequestDto|array $payload)
 * @method static \TechGenus\TgPay\DTO\Response\PaymentResponseDto getPayment(string $chargeId)
 * @method static \TechGenus\TgPay\DTO\Response\PaymentResponseDto capturePayment(string $chargeId, array $payload = [])
 * @method static \TechGenus\TgPay\DTO\Response\PaymentResponseDto cancelPayment(string $chargeId)
 * @method static \TechGenus\TgPay\DTO\Response\SubscriptionResponseDto createSubscription(\TechGenus\TgPay\DTO\Request\CreateSubscriptionRequestDto|array $payload)
 * @method static array createCheckoutSession(\TechGenus\TgPay\DTO\Request\CreateCheckoutSessionRequestDto|array $payload)
 * @method static \TechGenus\TgPay\DTO\Response\SubscriptionResponseDto getSubscription(string $subscriptionId)
 * @method static \TechGenus\TgPay\DTO\Response\BillingPortalResponseDto createBillingPortalSession(string $subscriptionId, \TechGenus\TgPay\DTO\Request\CreateBillingPortalSessionRequestDto|array|null $payload = null)
 * @method static \TechGenus\TgPay\DTO\Response\SubscriptionResponseDto cancelSubscription(string $subscriptionId)
 * @method static \TechGenus\TgPay\DTO\Response\SubscriptionOrdersResponseDto getSubscriptionOrders(string $subscriptionId, ?string $startDate = null, ?string $endDate = null)
 * @method static \TechGenus\TgPay\Config\TgPayConfig getConfig()
 *
 * @see Client
 */
class TgPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
