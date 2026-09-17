<?php

declare(strict_types=1);

namespace TechGenus\TgPay\DTO\Request;

final class PaymentMethodDto
{
    public const TYPE_CREDIT_CARD = 'credit_card';
    public const TYPE_PIX = 'pix';
    public const TYPE_BOLETO = 'boleto';

    /**
     * Formas que não passam pelo checkout hospedado do gateway.
     *
     * O Asaas só aceita cartão em chargeTypes RECURRENT, então boleto e pix
     * exigem POST /subscriptions direto — e devolvem uma cobrança para mostrar
     * ao cliente, em vez de uma URL para redirecionar.
     */
    public const TYPES_WITH_CHARGE = [self::TYPE_BOLETO, self::TYPE_PIX];

    /** A cobrança precisa ser mostrada ao cliente (boleto/pix)? */
    public function producesCharge(): bool
    {
        return in_array($this->type, self::TYPES_WITH_CHARGE, true);
    }

    public function __construct(
        public readonly string $type,
        public readonly ?CreditCardDto $card = null,
        public readonly ?int $installments = null,
        public readonly ?int $expirationMinutes = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $card = null;
        if (! empty($data['card']) && is_array($data['card'])) {
            $card = CreditCardDto::fromArray($data['card']);
        }

        return new self(
            type: $data['type'],
            card: $card,
            installments: $data['installments'] ?? null,
            expirationMinutes: $data['expiration_minutes'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = ['type' => $this->type];
        if ($this->card !== null) {
            $arr['card'] = $this->card->toArray();
        }
        if ($this->installments !== null) {
            $arr['installments'] = $this->installments;
        }
        if ($this->expirationMinutes !== null) {
            $arr['expiration_minutes'] = $this->expirationMinutes;
        }
        return $arr;
    }
}
