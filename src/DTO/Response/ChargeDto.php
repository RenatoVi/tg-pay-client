<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Response;

/**
 * Cobrança em aberto de uma assinatura — o que o cliente tem a pagar.
 *
 * Só existe em boleto e pix: cartão é debitado sozinho, não há o que mostrar.
 *
 * Assinatura por boleto/pix emite uma cobrança NOVA a cada ciclo, então esta
 * vence: a da criação da assinatura vale só para o primeiro ciclo, e as
 * seguintes saem de getSubscriptionCharge().
 */
final class ChargeDto
{
    public const TYPE_BOLETO = 'boleto';
    public const TYPE_PIX = 'pix';

    public function __construct(
        public readonly string $billingType,
        public readonly ?string $dueDate = null,
        public readonly ?float $value = null,
        /** PDF do boleto. Nulo em pix. */
        public readonly ?string $bankSlipUrl = null,
        /** Página de pagamento hospedada pelo gateway. Existe nos dois tipos. */
        public readonly ?string $invoiceUrl = null,
        public readonly ?string $status = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            billingType: (string) ($data['billing_type'] ?? ''),
            dueDate: $data['due_date'] ?? null,
            value: isset($data['value']) ? (float) $data['value'] : null,
            bankSlipUrl: $data['bank_slip_url'] ?? null,
            invoiceUrl: $data['invoice_url'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    public function isBoleto(): bool
    {
        return $this->billingType === self::TYPE_BOLETO;
    }

    public function isPix(): bool
    {
        return $this->billingType === self::TYPE_PIX;
    }

    /** Link para mandar ao cliente: o PDF quando há, senão a fatura. */
    public function payableUrl(): ?string
    {
        return $this->bankSlipUrl ?? $this->invoiceUrl;
    }
}
