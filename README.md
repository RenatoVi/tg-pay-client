# tech-genus/tg-pay-php-client

Cliente PHP/Laravel para a API **tg-pay** - pagamentos avulsos, assinaturas recorrentes e checkout sessions, com DTOs tipados, validação de webhooks via HMAC e integração automática com o service container do Laravel.

---

## Sumario

- [Requisitos](#requisitos)
- [Instalacao](#instalacao)
- [Configuracao](#configuracao)
  - [Laravel (auto-discovery)](#laravel-auto-discovery)
  - [Configuracao manual](#configuracao-manual)
- [Uso basico](#uso-basico)
  - [Facade](#facade)
  - [Injecao de dependencia](#injecao-de-dependencia)
- [Pagamentos](#pagamentos)
  - [Criar pagamento](#criar-pagamento)
  - [Pagamento via PIX](#pagamento-via-pix)
  - [Consultar pagamento](#consultar-pagamento)
  - [Capturar pagamento](#capturar-pagamento)
  - [Cancelar pagamento](#cancelar-pagamento)
- [Assinaturas](#assinaturas)
  - [Criar assinatura](#criar-assinatura)
  - [Consultar assinatura](#consultar-assinatura)
  - [Cancelar assinatura](#cancelar-assinatura)
  - [Pedidos da assinatura](#pedidos-da-assinatura)
  - [Billing Portal (Stripe)](#billing-portal-stripe)
- [Checkout Session](#checkout-session)
- [Outros metodos](#outros-metodos)
- [Webhooks](#webhooks)
  - [Webhook do merchant (eventos para sua aplicacao)](#webhook-do-merchant)
  - [Webhook do tg-pay (com verificacao HMAC)](#webhook-do-tg-pay)
- [Tratamento de erros](#tratamento-de-erros)
- [Referencia dos DTOs](#referencia-dos-dtos)
  - [Request DTOs](#request-dtos)
  - [Response DTOs](#response-dtos)
- [Desenvolvimento local](#desenvolvimento-local)

---

## Requisitos

- PHP >= 8.2
- Laravel 10, 11 ou 12 (utiliza `Illuminate\Support\Facades\Http` internamente)

---

## Instalacao

```bash
composer require tech-genus/tg-pay-php-client
```

---

## Configuracao

### Laravel (auto-discovery)

O pacote registra automaticamente o **Service Provider** e o alias **TgPay**. Publique o arquivo de configuracao:

```bash
php artisan vendor:publish --tag=tgpay-config
```

Adicione as variaveis ao `.env`:

```env
TGPAY_BASE_URL=https://api-tg-pay.example.com
TGPAY_API_KEY=sua-api-key
TGPAY_WEBHOOK_SECRET=secret-para-validar-webhooks
TGPAY_TIMEOUT=30
```

| Variavel              | Descricao                          | Default       |
|-----------------------|------------------------------------|---------------|
| `TGPAY_BASE_URL`      | URL base da API tg-pay             | `http://nginx`|
| `TGPAY_API_KEY`       | Chave de autenticacao (Bearer)     | `null`        |
| `TGPAY_WEBHOOK_SECRET`| Secret para validar HMAC webhooks  | `null`        |
| `TGPAY_TIMEOUT`       | Timeout HTTP em segundos           | `30`          |

### Configuracao manual

Para uso fora do Laravel ou sem publicar a config:

**Via objeto TgPayConfig:**

```php
use TechGenus\TgPay\Config\TgPayConfig;
use TechGenus\TgPay\Client;

$config = TgPayConfig::fromArray([
    'base_url'       => 'https://api-tg-pay.example.com',
    'api_key'        => 'sua-api-key',
    'webhook_secret' => 'secret',
    'timeout'        => 30,
]);

$client = new Client($config);
```

**Via parametros diretos:**

```php
$client = new Client(
    baseUrl: 'https://api-tg-pay.example.com',
    apiKey: 'sua-api-key',
);
```

---

## Uso basico

### Facade

```php
use TgPay;

$health       = TgPay::health();
$payment      = TgPay::createPayment($requestDto);
$subscription = TgPay::createSubscription($requestDto);
```

### Injecao de dependencia

```php
use TechGenus\TgPay\Client;

class PaymentController extends Controller
{
    public function __construct(private readonly Client $tgPay) {}

    public function store(Request $request)
    {
        $payment = $this->tgPay->createPayment($requestDto);
    }
}
```

> Todos os metodos que recebem DTOs tambem aceitam `array`. O client resolve automaticamente.

---

## Pagamentos

### Criar pagamento

```php
use TechGenus\TgPay\DTO\Request\CreatePaymentRequestDto;
use TechGenus\TgPay\DTO\Request\CustomerDto;
use TechGenus\TgPay\DTO\Request\PaymentMethodDto;
use TechGenus\TgPay\DTO\Request\CreditCardDto;

$request = new CreatePaymentRequestDto(
    merchant: 'meu-merchant',
    code: 'PED-001',
    amount: 99.90,
    currency: 'BRL',
    description: 'Pedido 001',
    customer: new CustomerDto(
        name: 'Joao Silva',
        email: 'joao@example.com',
        document: '12345678900',
        phone: '11999999999',
    ),
    paymentMethod: new PaymentMethodDto(
        type: PaymentMethodDto::TYPE_CREDIT_CARD,
        card: new CreditCardDto(
            number: '4111111111111111',
            holderName: 'JOAO SILVA',
            expirationMonth: '12',
            expirationYear: '2030',
            cvv: '123',
        ),
        installments: 1,
    ),
    autoCapture: true,
);

$payment = $client->createPayment($request);

// Acessar dados da resposta
echo $payment->id;          // ID da cobranca
echo $payment->status;      // ex: "authorized", "captured", "pending"
echo $payment->amount;      // 99.90
echo $payment->paidAt;      // data do pagamento (se capturado)
```

### Pagamento via PIX

```php
$request = new CreatePaymentRequestDto(
    merchant: 'meu-merchant',
    code: 'PED-002',
    amount: 50.00,
    currency: 'BRL',
    description: 'Pedido PIX',
    customer: new CustomerDto(
        name: 'Maria Santos',
        email: 'maria@example.com',
        document: '98765432100',
        phone: '11988888888',
    ),
    paymentMethod: new PaymentMethodDto(
        type: PaymentMethodDto::TYPE_PIX,
        expirationMinutes: 30, // tempo para expirar o QR code
    ),
    autoCapture: true,
);

$payment = $client->createPayment($request);
// $payment->details pode conter QR code e chave PIX
```

### Consultar pagamento

```php
$payment = $client->getPayment('charge_abc123');

echo $payment->status;        // status atual
echo $payment->transactionId; // ID da transacao no gateway
echo $payment->gateway;       // gateway utilizado
```

### Capturar pagamento

Para pagamentos criados com `autoCapture: false`:

```php
$payment = $client->capturePayment('charge_abc123');
```

### Cancelar pagamento

```php
$payment = $client->cancelPayment('charge_abc123');
```

---

## Assinaturas

### Criar assinatura

```php
use TechGenus\TgPay\DTO\Request\CreateSubscriptionRequestDto;

$request = new CreateSubscriptionRequestDto(
    merchant: 'meu-merchant',
    amount: 49.90,
    currency: 'BRL',
    description: 'Plano mensal',
    cycle: 'MONTHLY',
    nextDueDate: '2025-04-01',
    customer: new CustomerDto(
        name: 'Joao Silva',
        email: 'joao@example.com',
        document: '12345678900',
        phone: '11999999999',
    ),
    paymentMethod: new PaymentMethodDto(
        type: PaymentMethodDto::TYPE_CREDIT_CARD,
        card: new CreditCardDto(
            number: '4111111111111111',
            holderName: 'JOAO SILVA',
            expirationMonth: '12',
            expirationYear: '2030',
            cvv: '123',
        ),
    ),
    // Opcionais:
    // endDate: '2026-04-01',
    // maxPayments: 12,
);

$subscription = $client->createSubscription($request);

echo $subscription->subscriptionId; // ID da assinatura
echo $subscription->status;         // ex: "active", "pending"
echo $subscription->cycle;          // "MONTHLY"
```

Tambem aceita array:

```php
$subscription = $client->createSubscription([
    'merchant'       => 'meu-merchant',
    'amount'         => 49.90,
    'currency'       => 'BRL',
    'description'    => 'Plano mensal',
    'cycle'          => 'MONTHLY',
    'next_due_date'  => '2025-04-01',
    'customer'       => [
        'name'     => 'Joao Silva',
        'email'    => 'joao@example.com',
        'document' => '12345678900',
        'phone'    => '11999999999',
    ],
    'payment_method' => [
        'type' => 'credit_card',
        'card' => [
            'number'           => '4111111111111111',
            'holder_name'      => 'JOAO SILVA',
            'expiration_month' => '12',
            'expiration_year'  => '2030',
            'cvv'              => '123',
        ],
    ],
]);
```

### Consultar assinatura

```php
$subscription = $client->getSubscription('sub_123');

echo $subscription->status;     // "active", "canceled", etc.
echo $subscription->startedAt;  // data de inicio
echo $subscription->canceledAt; // null se ativa
```

### Cancelar assinatura

Agenda o cancelamento para o fim do periodo corrente:

```php
$subscription = $client->cancelSubscription('sub_123');
```

### Pedidos da assinatura

Lista os pedidos (cobranças) gerados pela assinatura:

```php
$orders = $client->getSubscriptionOrders(
    subscriptionId: 'sub_123',
    startDate: '2025-01-01', // opcional
    endDate: '2025-12-31',   // opcional
);

echo $orders->subscriptionId;

foreach ($orders->orders as $order) {
    echo $order->order_code;   // codigo do pedido
    echo $order->billing_date; // data de cobranca
    echo $order->amount;       // valor
    echo $order->status;       // status
    // $order->charges          // array de cobranças vinculadas
}
```

### Billing Portal (Stripe)

Gera uma URL para o Billing Portal do Stripe, onde o cliente pode gerenciar a assinatura:

```php
$portal = $client->createBillingPortalSession('17'); // subscription_id do tg-pay

return redirect($portal->url);
```

#### Upgrade/Downgrade de plano

Para direcionar o cliente diretamente para a tela de confirmacao de troca de plano (com proration calculada automaticamente pelo Stripe), passe o `priceId` do novo plano:

```php
use TechGenus\TgPay\DTO\Request\CreateBillingPortalSessionRequestDto;

$portal = $client->createBillingPortalSession('17', new CreateBillingPortalSessionRequestDto(
    priceId: 'price_anual_290', // Price ID do novo plano no Stripe
));

return redirect($portal->url);
// O cliente ve a tela do Stripe com o valor atual, novo valor e proration
```

Parametros opcionais disponiveis:

```php
$portal = $client->createBillingPortalSession('17', new CreateBillingPortalSessionRequestDto(
    priceId: 'price_anual_290',   // Price ID do novo plano (ativa flow de upgrade)
    returnUrl: 'https://meusite.com/conta', // URL de retorno customizada
    locale: 'pt-BR',              // Idioma do portal
));
```

Tambem aceita array:

```php
$portal = $client->createBillingPortalSession('17', [
    'price_id' => 'price_anual_290',
    'return_url' => 'https://meusite.com/conta',
    'locale' => 'pt-BR',
]);
```

> Quando `priceId` e informado, o Stripe usa o flow `subscription_update_confirm` — o cliente vai direto para a tela de confirmacao com o novo valor e proration calculada, sem precisar navegar pelo portal.

> Disponivel apenas para assinaturas processadas via Stripe.

---

## Checkout Session

Cria uma sessao de checkout hospedada:

```php
use TechGenus\TgPay\DTO\Request\CreateCheckoutSessionRequestDto;
use TechGenus\TgPay\DTO\Request\CustomerDto;

$request = new CreateCheckoutSessionRequestDto(
    amount: 99.90,
    cycle: 'MONTHLY',
    customer: new CustomerDto(
        name: 'Joao Silva',
        email: 'joao@example.com',
        document: '12345678900',
        phone: '11999999999',
    ),
    code: 'CHECKOUT-001',
    successUrl: 'https://meusite.com/assinatura/sucesso',
    cancelUrl: 'https://meusite.com/assinatura/cancelar',
    // Opcionais:
    // currency: 'BRL', (default: 'BRL')
    // description: 'Assinatura via checkout',
    // metadata: ['referral' => 'campaign-x'],
);

$session = $client->createCheckoutSession($request);
// $session e um array com dados do checkout (URL, etc.)
```

---

## Outros metodos

```php
// Health check
$health = $client->health();
echo $health->status;    // "ok"
echo $health->timestamp;

// Listar bancos disponiveis
$banks = $client->getBanks();
foreach ($banks as $bank) {
    echo $bank->code; // codigo do banco
    echo $bank->name; // nome do banco
}

// Obter config atual do client
$config = $client->getConfig();
echo $config->baseUrl;
```

---

## Webhooks

O pacote oferece dois mecanismos de webhook:

### Webhook do merchant

Eventos enviados pelo tg-pay para a `webhook_url` configurada no merchant. O body do POST tem o formato:

```json
{
    "webhook_type": "subscription",
    "event": "subscription.activated",
    "subscription_id": 17,
    "metadata": {
        "order_id": "1683",
        "order_number": "7",
        "cycle": "MONTHLY"
    }
}
```

Para eventos `subscription.updated` (upgrade/downgrade), o payload inclui dados adicionais:

```json
{
    "webhook_type": "subscription",
    "event": "subscription.updated",
    "subscription_id": 17,
    "metadata": { ... },
    "amount": 290,
    "currency": "BRL",
    "interval": "year",
    "price_id": "price_anual_290"
}
```

**Eventos de assinatura disponiveis:**

| Constante                                          | Valor                         |
|---------------------------------------------------|-------------------------------|
| `MerchantWebhookEvent::SUBSCRIPTION_ACTIVATED`      | `subscription.activated`      |
| `MerchantWebhookEvent::SUBSCRIPTION_RENEWED`        | `subscription.renewed`        |
| `MerchantWebhookEvent::SUBSCRIPTION_PAYMENT_FAILED` | `subscription.payment_failed` |
| `MerchantWebhookEvent::SUBSCRIPTION_CANCELED`       | `subscription.canceled`       |
| `MerchantWebhookEvent::SUBSCRIPTION_UPDATED`       | `subscription.updated`        |

**Tipos de webhook:**

| Constante                           | Valor          |
|------------------------------------|----------------|
| `MerchantWebhookType::SUBSCRIPTION` | `subscription` |
| `MerchantWebhookType::PAYMENT`      | `payment`      |

**Exemplo de uso com MerchantWebhookHandler (recomendado):**

1. Crie um listener com sua regra de negocio:

```php
// app/Listeners/TgPayWebhookListener.php
use TechGenus\TgPay\Webhook\MerchantWebhookListener;

class TgPayWebhookListener extends MerchantWebhookListener
{
    public function onSubscriptionActivated(int $subscriptionId, ?array $metadata = null): void
    {
        // $metadata contem os dados enviados no checkout: order_id, order_number, etc.
        $orderId = $metadata['order_id'] ?? null;

        $user = User::where('subscription_id', $subscriptionId)->first();
        $user->update(['plan_status' => 'active']);
    }

    public function onSubscriptionRenewed(int $subscriptionId, ?array $metadata = null): void
    {
        // Renovar plano
        $user = User::where('subscription_id', $subscriptionId)->first();
        $user->update(['plan_expires_at' => now()->addMonth()]);
    }

    public function onSubscriptionPaymentFailed(int $subscriptionId, ?array $metadata = null): void
    {
        // Notificar falha no pagamento
    }

    public function onSubscriptionCanceled(int $subscriptionId, ?array $metadata = null): void
    {
        // Cancelar plano
        $user = User::where('subscription_id', $subscriptionId)->first();
        $user->update(['plan_status' => 'canceled']);
    }

    public function onSubscriptionUpdated(int $subscriptionId, ?array $metadata = null): void
    {
        // Cliente trocou de plano (upgrade/downgrade)
        // $metadata contem: amount, currency, interval, price_id
        $user = User::where('subscription_id', $subscriptionId)->first();
        $user->update([
            'plan_amount' => $metadata['amount'] ?? $user->plan_amount,
            'plan_interval' => $metadata['interval'] ?? $user->plan_interval,
        ]);
    }
}
```

2. Registre a rota:

```php
// routes/api.php
use TechGenus\TgPay\Webhook\MerchantWebhookHandler;
use App\Listeners\TgPayWebhookListener;

Route::post('/webhooks/tg-pay', fn(Request $r) =>
    MerchantWebhookHandler::handle($r, new TgPayWebhookListener())
);
```

> Sobrescreva apenas os metodos que precisa. Os demais sao ignorados automaticamente.

**Exemplo manual (sem handler):**

```php
use TechGenus\TgPay\DTO\Response\MerchantWebhookPayloadDto;
use TechGenus\TgPay\Webhook\MerchantWebhookEvent;

// No controller que recebe o webhook
public function handleWebhook(Request $request)
{
    $payload = MerchantWebhookPayloadDto::fromArray($request->all());

    if ($payload->isSubscription()) {
        match ($payload->event) {
            MerchantWebhookEvent::SUBSCRIPTION_ACTIVATED     => $this->ativar($payload->subscriptionId),
            MerchantWebhookEvent::SUBSCRIPTION_RENEWED       => $this->renovar($payload->subscriptionId),
            MerchantWebhookEvent::SUBSCRIPTION_PAYMENT_FAILED => $this->falhou($payload->subscriptionId),
            MerchantWebhookEvent::SUBSCRIPTION_CANCELED      => $this->cancelar($payload->subscriptionId),
            MerchantWebhookEvent::SUBSCRIPTION_UPDATED       => $this->atualizar($payload->subscriptionId, $payload->metadata),
            default => null,
        };
    }

    return response()->json(['ok' => true]);
}
```

### Webhook do tg-pay

Webhooks enviados diretamente pelo tg-pay com verificacao de assinatura HMAC SHA-256 via header `X-TgPay-Signature`.

**Eventos disponiveis:**

| Constante                                   | Valor                          |
|---------------------------------------------|--------------------------------|
| `WebhookEvent::PAYMENT_STATUS`              | `payment_status`               |
| `WebhookEvent::SUBSCRIPTION_PAYMENT`        | `subscription_payment`         |
| `WebhookEvent::PIX_PAYMENT`                 | `pix_payment`                  |
| `WebhookEvent::CHECKOUT_SESSION_COMPLETED`  | `checkout_session_completed`   |

**Exemplo com verificacao:**

```php
use TechGenus\TgPay\Webhook\WebhookVerifier;
use TechGenus\TgPay\Webhook\WebhookEvent;
use TechGenus\TgPay\TgPayException;

public function handleTgPayWebhook(Request $request)
{
    $verifier = new WebhookVerifier(
        webhookSecret: config('tgpay.webhook_secret'),
    );

    try {
        $event = $verifier->verify(
            rawBody: $request->getContent(),
            signatureHeader: $request->header(WebhookVerifier::SIGNATURE_HEADER),
        );
    } catch (TgPayException $e) {
        // Assinatura ausente, invalida ou payload mal-formado
        return response()->json(['error' => $e->getMessage()], 400);
    }

    // $event->type      string  - tipo do evento
    // $event->data      array   - payload com dados do recurso
    // $event->timestamp ?string - timestamp opcional

    match ($event->type) {
        WebhookEvent::PAYMENT_STATUS             => $this->handlePayment($event->data),
        WebhookEvent::SUBSCRIPTION_PAYMENT       => $this->handleSubscription($event->data),
        WebhookEvent::PIX_PAYMENT                => $this->handlePix($event->data),
        WebhookEvent::CHECKOUT_SESSION_COMPLETED => $this->handleCheckout($event->data),
        default => null,
    };

    return response()->json(['ok' => true]);
}
```

---

## Tratamento de erros

Todas as chamadas HTTP que falharem lancam `TgPayException`:

```php
use TechGenus\TgPay\TgPayException;

try {
    $payment = $client->createPayment($request);
} catch (TgPayException $e) {
    echo $e->getMessage(); // mensagem da API ou body da resposta
    echo $e->getCode();    // HTTP status code (400, 401, 422, 500, etc.)
}
```

Erros especificos de webhook:

| Metodo estatico                              | Cenario                          |
|---------------------------------------------|----------------------------------|
| `TgPayException::webhookSignatureMissing()` | Header de assinatura ausente     |
| `TgPayException::webhookSignatureInvalid()` | HMAC nao confere                 |
| `TgPayException::webhookInvalidPayload()`   | JSON do body invalido ou ausente |

---

## Referencia dos DTOs

### Request DTOs

#### `CreatePaymentRequestDto`

| Propriedade     | Tipo              | Obrigatorio |
|----------------|-------------------|-------------|
| `merchant`     | `string`          | Sim         |
| `code`         | `string`          | Sim         |
| `amount`       | `float`           | Sim         |
| `currency`     | `string`          | Sim         |
| `description`  | `string`          | Sim         |
| `customer`     | `CustomerDto`     | Sim         |
| `paymentMethod`| `PaymentMethodDto` | Sim        |
| `autoCapture`  | `bool`            | Sim         |
| `metadata`     | `?array`          | Nao         |

#### `CreateSubscriptionRequestDto`

| Propriedade     | Tipo              | Obrigatorio |
|----------------|-------------------|-------------|
| `merchant`     | `string`          | Sim         |
| `amount`       | `float`           | Sim         |
| `currency`     | `string`          | Sim         |
| `description`  | `string`          | Sim         |
| `cycle`        | `string`          | Sim         |
| `nextDueDate`  | `string`          | Sim         |
| `customer`     | `CustomerDto`     | Sim         |
| `paymentMethod`| `PaymentMethodDto` | Sim        |
| `endDate`      | `?string`         | Nao         |
| `maxPayments`  | `?int`            | Nao         |

#### `CreateCheckoutSessionRequestDto`

| Propriedade   | Tipo          | Obrigatorio | Default |
|--------------|---------------|-------------|---------|
| `amount`     | `float`       | Sim         |         |
| `cycle`      | `string`      | Sim         |         |
| `customer`   | `CustomerDto` | Sim         |         |
| `code`       | `string`      | Sim         |         |
| `successUrl` | `string`      | Sim         |         |
| `cancelUrl`  | `string`      | Sim         |         |
| `currency`   | `string`      | Nao         | `'BRL'` |
| `description`| `?string`     | Nao         |         |
| `metadata`   | `?array`      | Nao         |         |

#### `CreateBillingPortalSessionRequestDto`

| Propriedade | Tipo      | Obrigatorio | Descricao                                                |
|------------|-----------|-------------|----------------------------------------------------------|
| `priceId`  | `?string` | Nao         | Price ID do Stripe para upgrade/downgrade (ativa flow)   |
| `returnUrl`| `?string` | Nao         | URL de retorno customizada                               |
| `locale`   | `?string` | Nao         | Idioma do portal (ex: `pt-BR`)                           |

#### `CustomerDto`

| Propriedade | Tipo          | Obrigatorio |
|------------|---------------|-------------|
| `name`     | `string`      | Sim         |
| `email`    | `string`      | Sim         |
| `document` | `?string`     | Nao         |
| `phone`    | `?string`     | Nao         |
| `dob`      | `?string`     | Nao         |
| `address`  | `?AddressDto` | Nao         |

#### `AddressDto`

| Propriedade    | Tipo      | Obrigatorio |
|---------------|-----------|-------------|
| `street`      | `?string` | Nao         |
| `number`      | `?string` | Nao         |
| `neighborhood`| `?string` | Nao         |
| `city`        | `?string` | Nao         |
| `state`       | `?string` | Nao         |
| `country`     | `?string` | Nao         |
| `zipCode`     | `?string` | Nao         |
| `complement`  | `?string` | Nao         |

#### `PaymentMethodDto`

| Propriedade         | Tipo             | Obrigatorio |
|--------------------|------------------|-------------|
| `type`             | `string`         | Sim         |
| `card`             | `?CreditCardDto` | Nao*        |
| `installments`     | `?int`           | Nao         |
| `expirationMinutes`| `?int`           | Nao         |

\* Obrigatorio quando `type = 'credit_card'`.

**Constantes:** `TYPE_CREDIT_CARD = 'credit_card'`, `TYPE_PIX = 'pix'`

#### `CreditCardDto`

| Propriedade       | Tipo     | Obrigatorio |
|------------------|----------|-------------|
| `number`         | `string` | Sim         |
| `holderName`     | `string` | Sim         |
| `expirationMonth`| `string` | Sim         |
| `expirationYear` | `string` | Sim         |
| `cvv`            | `string` | Sim         |

### Response DTOs

#### `PaymentResponseDto`

| Propriedade     | Tipo      |
|----------------|-----------|
| `id`           | `string`  |
| `orderCode`    | `?string` |
| `gateway`      | `?string` |
| `paymentMethod`| `?string` |
| `amount`       | `float`   |
| `status`       | `string`  |
| `transactionId`| `?string` |
| `installments` | `?int`    |
| `errorMessage` | `?string` |
| `paidAt`       | `?string` |
| `details`      | `?array`  |
| `events`       | `?array`  |
| `createdAt`    | `?string` |
| `updatedAt`    | `?string` |

#### `SubscriptionResponseDto`

| Propriedade      | Tipo      |
|-----------------|-----------|
| `subscriptionId` | `string` |
| `status`        | `string`  |
| `cycle`         | `?string` |
| `startedAt`     | `?string` |
| `canceledAt`    | `?string` |

#### `SubscriptionOrdersResponseDto`

| Propriedade      | Tipo     |
|-----------------|----------|
| `subscriptionId` | `string`|
| `orders`        | `array`  |

Cada item de `orders` contem: `order_code` (string), `billing_date` (?string), `amount` (float), `status` (string), `charges` (array).

#### `BillingPortalResponseDto`

| Propriedade | Tipo     |
|------------|----------|
| `url`      | `string` |

#### `HealthResponseDto`

| Propriedade  | Tipo      |
|-------------|-----------|
| `status`    | `string`  |
| `timestamp` | `?string` |

#### `BankItemDto`

| Propriedade | Tipo     |
|------------|----------|
| `code`     | `string` |
| `name`     | `string` |

#### `ApiResponseDto`

| Propriedade | Tipo      |
|------------|-----------|
| `success`  | `bool`    |
| `data`     | `?array`  |
| `message`  | `?string` |

#### `MerchantWebhookPayloadDto`

| Propriedade      | Tipo      |
|-----------------|-----------|
| `webhookType`   | `string`  |
| `event`         | `string`  |
| `subscriptionId`| `int`     |
| `metadata`      | `?array`  |

**Metodos:** `isSubscription(): bool`, `isPayment(): bool`

---

## Desenvolvimento local

O pacote fica em `package/tg-pay-php-client` dentro do monorepo tg-pay. E referenciado via path repository em `require-dev`; alteracoes refletem imediatamente (symlink).

Em producao, `composer install --no-dev` exclui o cliente do deploy.

### Testar criacao de assinatura

```bash
# Subir a API
./start.sh

# Testar assinatura
./a tg-pay:test-subscription meu-merchant
./a tg-pay:test-subscription meu-merchant --cycle=MONTHLY --amount=49.90
./a tg-pay:test-subscription meu-merchant --base-url=http://nginx
```

### Testar checkout session

```bash
# Criar sessao de checkout (gera dados do cliente automaticamente)
./a package:checkout-session --api-key=SUA_API_KEY

# Com parametros customizados
./a package:checkout-session --api-key=SUA_API_KEY --amount=49.90 --cycle=MONTHLY --name="Joao Silva" --email=joao@example.com
```

### Testar webhook

Dispara um webhook de teste para validar se a API externa esta recebendo corretamente:

```bash
# Default (subscription.activated, subscription_id=1, url do config/merchants.php)
./a package:test-webhook

# Testar evento especifico
./a package:test-webhook --event=subscription.renewed --subscription-id=38

# Testar com URL customizada
./a package:test-webhook --url=https://minha-api.com/webhooks/tg-pay --event=subscription.canceled

# Eventos disponiveis:
# subscription.activated, subscription.renewed, subscription.payment_failed, subscription.canceled, subscription.updated
```
