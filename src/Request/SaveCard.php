<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A card kept for a customer without a payment being made on it. The
 * provider is told who the card belongs to, so the token it hands back is
 * held under that customer and the card can be charged again later.
 *
 * Providers without a card store of their own keep a card by charging one
 * lira and giving it straight back; those ask for the security code, and
 * the ones with a real card store do not.
 */
final readonly class SaveCard extends ChannelMessage
{
    public function __construct(
        public Customer $customer,
        public Card $card,
        /** The payment account to keep the card at. Left out, the team's default account is used. */
        public ?int $paymentProviderId = null,
        ?int $channelId = null,
    ) {
        parent::__construct($channelId);
    }

    public function path(): string
    {
        return 'save-card';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        $card = $this->card->toArray();
        unset($card['should_save']);

        if ($card['security_code'] === '') {
            unset($card['security_code']);
        }

        return [
            'saved_card' => self::said([
                'channel_id' => $this->channel($channelId),
                'payment_provider_id' => $this->paymentProviderId,
            ]),
            'customer' => $this->customer->toArray(),
            'card' => $card,
        ];
    }
}
