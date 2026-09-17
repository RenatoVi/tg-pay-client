<?php

declare(strict_types=1);

namespace TechGenus\TgPay;

use Illuminate\Support\Facades\Http;
use TechGenus\TgPay\Config\TgPayConfig;
use TechGenus\TgPay\DTO\Request\AddSubscriptionItemRequestDto;
use TechGenus\TgPay\DTO\Request\CreateBillingPortalSessionRequestDto;
use TechGenus\TgPay\DTO\Request\CreateCheckoutSessionRequestDto;
use TechGenus\TgPay\DTO\Request\CreatePaymentRequestDto;
use TechGenus\TgPay\DTO\Request\CreateSubscriptionRequestDto;
use TechGenus\TgPay\DTO\Request\UpdateSubscriptionItemRequestDto;
use TechGenus\TgPay\DTO\Request\UpdateSubscriptionRequestDto;
use TechGenus\TgPay\DTO\Response\ChargeDto;
use TechGenus\TgPay\DTO\Response\BankItemDto;
use TechGenus\TgPay\DTO\Response\BillingPortalResponseDto;
use TechGenus\TgPay\DTO\Response\HealthResponseDto;
use TechGenus\TgPay\DTO\Response\PaymentResponseDto;
use TechGenus\TgPay\DTO\Response\SubscriptionItemResponseDto;
use TechGenus\TgPay\DTO\Response\SubscriptionOrdersResponseDto;
use TechGenus\TgPay\DTO\Response\SubscriptionResponseDto;

class Client
{
    private string $baseUrl;
    private ?string $apiKey;
    private int $timeout;

    public function __construct(
        string|TgPayConfig $baseUrlOrConfig,
        ?string $apiKey = null,
    ) {
        if ($baseUrlOrConfig instanceof TgPayConfig) {
            $this->baseUrl = rtrim($baseUrlOrConfig->baseUrl, '/');
            $this->apiKey = $baseUrlOrConfig->apiKey;
            $this->timeout = $baseUrlOrConfig->timeout;
        } else {
            $this->baseUrl = rtrim($baseUrlOrConfig, '/');
            $this->apiKey = $apiKey;
            $this->timeout = 30;
        }
    }

    public function health(): HealthResponseDto
    {
        $raw = $this->get('/health');
        $data = $raw['data'] ?? $raw;
        return HealthResponseDto::fromArray(is_array($data) ? $data : ['status' => '']);
    }

    public function getBanks(): array
    {
        $raw = $this->get('/banks');
        $items = $raw['data'] ?? $raw;
        if (! is_array($items)) {
            return [];
        }
        return array_map(fn (array $item) => BankItemDto::fromArray($item), $items);
    }

    public function createPayment(CreatePaymentRequestDto|array $payload): PaymentResponseDto
    {
        $body = $payload instanceof CreatePaymentRequestDto ? $payload->toArray() : $payload;
        $raw = $this->post('/payment', $body);
        return $this->paymentFromResponse($raw);
    }

    public function getPayment(string $chargeId): PaymentResponseDto
    {
        $raw = $this->get("/payment/{$chargeId}");
        return $this->paymentFromResponse($raw);
    }

    public function capturePayment(string $chargeId, array $payload = []): PaymentResponseDto
    {
        $raw = $this->post("/payment/{$chargeId}/capture", $payload);
        return $this->paymentFromResponse($raw);
    }

    public function cancelPayment(string $chargeId): PaymentResponseDto
    {
        $raw = $this->post("/payment/{$chargeId}/cancel", []);
        return $this->paymentFromResponse($raw);
    }

    public function createSubscription(CreateSubscriptionRequestDto|array $payload): SubscriptionResponseDto
    {
        $body = $payload instanceof CreateSubscriptionRequestDto ? $payload->toArray() : $payload;
        $raw = $this->post('/subscriptions', $body);
        return $this->subscriptionFromResponse($raw);
    }

    /**
     * Cobrança em aberto da assinatura (boleto/pix).
     *
     * Consulta o gateway a cada chamada de propósito: boleto e pix emitem uma
     * cobrança NOVA a cada ciclo, então a devolvida na criação da assinatura
     * vence depois da primeira renovação.
     *
     * Devolve null quando não há nada em aberto — tudo pago, ou assinatura de
     * cartão, que é debitada sozinha.
     */
    public function getSubscriptionCharge(string $subscriptionId): ?ChargeDto
    {
        $raw = $this->get("/subscriptions/{$subscriptionId}/charge");
        $data = $raw['data'] ?? null;

        return ! empty($data) && is_array($data) ? ChargeDto::fromArray($data) : null;
    }

    public function createCheckoutSession(CreateCheckoutSessionRequestDto|array $payload): array
    {
        $body = $payload instanceof CreateCheckoutSessionRequestDto ? $payload->toArray() : $payload;
        return $this->post('/subscriptions/checkout-session', $body);
    }

    public function getSubscription(string $subscriptionId): SubscriptionResponseDto
    {
        $raw = $this->get("/subscriptions/{$subscriptionId}");
        return $this->subscriptionFromResponse($raw);
    }

    /**
     * Altera o valor da assinatura no gateway.
     *
     * Vale para gateway que cobra um valor unico por assinatura (Asaas).
     * Quem trabalha por itens (Stripe) usa updateSubscriptionItem.
     */
    public function updateSubscription(string $subscriptionId, UpdateSubscriptionRequestDto|array $payload): SubscriptionResponseDto
    {
        $body = $payload instanceof UpdateSubscriptionRequestDto ? $payload->toArray() : $payload;
        $raw = $this->patch("/subscriptions/{$subscriptionId}", $body);
        return $this->subscriptionFromResponse($raw);
    }

    public function createBillingPortalSession(string $subscriptionId, CreateBillingPortalSessionRequestDto|array|null $payload = null): BillingPortalResponseDto
    {
        $body = match (true) {
            $payload instanceof CreateBillingPortalSessionRequestDto => $payload->toArray(),
            is_array($payload) => $payload,
            default => [],
        };
        $raw = $this->post("/subscriptions/{$subscriptionId}/billing-portal", $body);
        $data = $raw['data'] ?? $raw;
        return BillingPortalResponseDto::fromArray(is_array($data) ? $data : []);
    }

    public function cancelSubscription(string $subscriptionId): SubscriptionResponseDto
    {
        $raw = $this->post("/subscriptions/{$subscriptionId}/cancel-subscription", []);
        return $this->subscriptionFromResponse($raw);
    }

    public function getSubscriptionOrders(string $subscriptionId, ?string $startDate = null, ?string $endDate = null): SubscriptionOrdersResponseDto
    {
        $query = array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ], fn ($v) => $v !== null && $v !== '');
        $raw = $this->get("/subscriptions/{$subscriptionId}/orders", $query);
        $data = $raw['data'] ?? $raw;
        $arr = is_array($data) ? $data : ['subscription_id' => $subscriptionId, 'orders' => []];
        if (! isset($arr['subscription_id'])) {
            $arr['subscription_id'] = $subscriptionId;
        }
        return SubscriptionOrdersResponseDto::fromArray($arr);
    }

    /**
     * @return SubscriptionItemResponseDto[]
     */
    public function listSubscriptionItems(string $subscriptionId): array
    {
        $raw = $this->get("/subscriptions/{$subscriptionId}/items");
        $items = $raw['data'] ?? $raw;
        if (! is_array($items)) {
            return [];
        }
        return array_map(fn (array $item) => SubscriptionItemResponseDto::fromArray($item), $items);
    }

    public function addSubscriptionItem(string $subscriptionId, AddSubscriptionItemRequestDto|array $payload): SubscriptionItemResponseDto
    {
        $body = $payload instanceof AddSubscriptionItemRequestDto ? $payload->toArray() : $payload;
        $raw = $this->post("/subscriptions/{$subscriptionId}/items", $body);
        $data = $raw['data'] ?? $raw;
        return SubscriptionItemResponseDto::fromArray(is_array($data) ? $data : []);
    }

    public function updateSubscriptionItem(string $subscriptionId, string $itemId, UpdateSubscriptionItemRequestDto|array $payload): SubscriptionItemResponseDto
    {
        $body = $payload instanceof UpdateSubscriptionItemRequestDto ? $payload->toArray() : $payload;
        $raw = $this->patch("/subscriptions/{$subscriptionId}/items/{$itemId}", $body);
        $data = $raw['data'] ?? $raw;
        return SubscriptionItemResponseDto::fromArray(is_array($data) ? $data : []);
    }

    public function removeSubscriptionItem(string $subscriptionId, string $itemId, ?string $prorationBehavior = null): void
    {
        $body = [];
        if ($prorationBehavior !== null) {
            $body['proration_behavior'] = $prorationBehavior;
        }
        $this->delete("/subscriptions/{$subscriptionId}/items/{$itemId}", $body);
    }

    public function getConfig(): TgPayConfig
    {
        return new TgPayConfig(
            baseUrl: $this->baseUrl,
            apiKey: $this->apiKey,
            webhookSecret: null,
            timeout: $this->timeout,
        );
    }

    private function paymentFromResponse(array $raw): PaymentResponseDto
    {
        $data = $raw['data'] ?? $raw;
        return PaymentResponseDto::fromArray(is_array($data) ? $data : []);
    }

    private function subscriptionFromResponse(array $raw): SubscriptionResponseDto
    {
        $data = $raw['data'] ?? $raw;
        return SubscriptionResponseDto::fromArray(is_array($data) ? $data : []);
    }

    private function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, [], $query);
    }

    private function post(string $path, array $body): array
    {
        return $this->request('POST', $path, $body);
    }

    private function patch(string $path, array $body): array
    {
        return $this->request('PATCH', $path, $body);
    }

    private function delete(string $path, array $body = []): array
    {
        return $this->request('DELETE', $path, $body);
    }

    private function request(string $method, string $path, array $body = [], array $query = []): array
    {
        $path = 'api/' . ltrim($path, '/');
        $uri = rtrim($this->baseUrl, '/') . '/' . $path;
        if ($query !== []) {
            $uri .= '?' . http_build_query($query);
        }

        $http = Http::timeout($this->timeout)
            ->acceptJson()
            ->withHeaders(['Content-Type' => 'application/json']);
        if ($this->apiKey !== null) {
            $http = $http->withToken($this->apiKey);
        }

        $response = match ($method) {
            'GET' => $http->get($uri),
            'PATCH' => $http->patch($uri, $body !== [] ? $body : []),
            'DELETE' => $http->delete($uri, $body !== [] ? $body : []),
            default => $http->post($uri, $body !== [] ? $body : []),
        };

        if ($response->failed()) {
            $decoded = $response->json();
            $message = is_array($decoded) && isset($decoded['message']) ? $decoded['message'] : $response->body();
            throw new TgPayException($message, $response->status());
        }

        $decoded = $response->json();
        return is_array($decoded) ? $decoded : [];
    }
}
