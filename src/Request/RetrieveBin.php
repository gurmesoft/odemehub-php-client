<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A question about a card before anything is charged to it: who issued it,
 * what kind of card it is, and how the amount may be paid off on it. Only
 * the head of the number is sent, never the whole of it, because nothing
 * is being charged.
 *
 * The body reads like the start of a payment, so the same account and
 * amount that would be paid are named here.
 */
final readonly class RetrieveBin extends Message
{
    public function __construct(
        /**
         * The first six to eight digits of the card. Six is what the banks
         * key their tables on; eight is what the gateway keeps of a card it
         * has been paid with, so a stored card's digits can be sent as they
         * are.
         */
        public string $bin,
        /** What the payment would come to, as digits with the kurus behind a point: '1000.00'. The instalments are priced on it. */
        public string $amount,
        /** The account to ask, or null for the merchant's default one. */
        public ?int $paymentProviderId = null,
        /** The money the payment would be taken in; lira unless another is named. */
        public ?string $currency = null,
    ) {}

    public function path(): string
    {
        return 'retrieve-bin';
    }

    /**
     * The body. What the caller left unsaid is left out altogether rather
     * than sent empty, so the gateway fills it in itself.
     *
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        return [
            'transaction' => self::said([
                'payment_provider_id' => $this->paymentProviderId,
                'amount' => $this->amount,
                'currency' => $this->currency,
            ]),
            'card' => ['bin' => $this->bin],
        ];
    }
}
