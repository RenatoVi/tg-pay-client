<?php

/**
 * Verificação dos DTOs sem dependência de rede nem de framework.
 *
 * O pacote não tem suíte; isto cobre o que quebraria silenciosamente ao
 * publicar: o contrato de boleto/pix e a compatibilidade com quem já usa a
 * v1.1 (payload de cartão e resposta sem charge).
 *
 * Rodar:  php tests/dto-smoke.php
 */
spl_autoload_register(function ($c) {
    $p = str_replace(['TechGenus\\TgPay\\', '\\'], ['', '/'], $c);
    $f = __DIR__.'/../src/'.$p.'.php';
    if (file_exists($f)) require $f;
});
use TechGenus\TgPay\DTO\Request\CreateSubscriptionRequestDto;
use TechGenus\TgPay\DTO\Request\PaymentMethodDto;
use TechGenus\TgPay\DTO\Response\ChargeDto;
use TechGenus\TgPay\DTO\Response\SubscriptionResponseDto;

$ok = 0; $fail = 0;
function check(string $nome, $esperado, $real) {
    global $ok, $fail;
    if ($esperado === $real) { $ok++; echo "  ok  $nome\n"; }
    else { $fail++; echo "  FALHA $nome: esperado ".var_export($esperado,true)." veio ".var_export($real,true)."\n"; }
}

echo "PaymentMethodDto\n";
check('boleto produz cobranca', true, (new PaymentMethodDto(type: PaymentMethodDto::TYPE_BOLETO))->producesCharge());
check('pix produz cobranca', true, (new PaymentMethodDto(type: PaymentMethodDto::TYPE_PIX))->producesCharge());
check('cartao NAO produz cobranca', false, (new PaymentMethodDto(type: PaymentMethodDto::TYPE_CREDIT_CARD))->producesCharge());

echo "CreateSubscriptionRequestDto\n";
$semData = CreateSubscriptionRequestDto::fromArray([
    'merchant' => 'm1', 'amount' => 197.0, 'currency' => 'BRL', 'description' => 'Plano',
    'cycle' => 'MONTHLY',
    'customer' => ['name'=>'C','email'=>'c@e.com','document'=>'123','phone'=>'11999999999'],
    'payment_method' => ['type' => 'boleto'],
]);
check('sem next_due_date nao quebra', null, $semData->nextDueDate);
check('next_due_date fora do payload', false, array_key_exists('next_due_date', $semData->toArray()));
check('payment_method.type=boleto', 'boleto', $semData->toArray()['payment_method']['type']);

$comData = CreateSubscriptionRequestDto::fromArray([
    'merchant' => 'm1', 'amount' => 197.0, 'currency' => 'BRL', 'description' => 'Plano',
    'cycle' => 'MONTHLY', 'next_due_date' => '2026-10-01',
    'customer' => ['name'=>'C','email'=>'c@e.com','document'=>'123','phone'=>'11999999999'],
    'payment_method' => ['type' => 'credit_card', 'card' => ['number'=>'4111111111111111','holder_name'=>'C','expiration_month'=>'12','expiration_year'=>'2030','cvv'=>'123']],
    'metadata' => ['order_id' => '7'],
]);
check('compat: next_due_date preservado', '2026-10-01', $comData->toArray()['next_due_date']);
check('metadata viaja', '7', $comData->toArray()['metadata']['order_id']);
check('cartao mantem o card', '4111111111111111', $comData->toArray()['payment_method']['card']['number']);

echo "ChargeDto / SubscriptionResponseDto\n";
$boleto = ChargeDto::fromArray(['billing_type'=>'boleto','due_date'=>'2026-10-01','value'=>197.0,'bank_slip_url'=>'https://b/pdf','invoice_url'=>'https://i','status'=>'pending']);
check('isBoleto', true, $boleto->isBoleto());
check('payableUrl prefere o PDF', 'https://b/pdf', $boleto->payableUrl());
$pix = ChargeDto::fromArray(['billing_type'=>'pix','invoice_url'=>'https://i/pix']);
check('isPix', true, $pix->isPix());
check('pix cai na fatura', 'https://i/pix', $pix->payableUrl());

$comCharge = SubscriptionResponseDto::fromArray(['subscription_id'=>'s1','status'=>'active','charge'=>['billing_type'=>'boleto','bank_slip_url'=>'https://b/x']]);
check('charge vira DTO', 'https://b/x', $comCharge->charge?->bankSlipUrl);
$semCharge = SubscriptionResponseDto::fromArray(['subscription_id'=>'s1','status'=>'active']);
check('compat: sem charge = null', null, $semCharge->charge);
$chargeVazio = SubscriptionResponseDto::fromArray(['subscription_id'=>'s1','status'=>'active','charge'=>[]]);
check('charge vazio = null', null, $chargeVazio->charge);

echo "\n$ok ok, $fail falhas\n";
exit($fail > 0 ? 1 : 0);
