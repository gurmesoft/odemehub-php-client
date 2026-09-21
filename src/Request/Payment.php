<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use InvalidArgumentException;

/**
 * A payment handed to the gateway. What is common to every kind of payment
 * lives here; the endpoint it is sent to is what tells the kinds apart.
 *
 * A payment is made with a card the customer typed in or with one they let
 * the merchant keep, never with both: naming a kept card and a card at once
 * is turned down by the gateway, so it is turned down here first.
 */
abstract readonly class Payment extends ChannelMessage
{
    public function __construct(
        /** The number the order is known by in the calling system. */
        public int $channelReference,
        /**
         * The amount, as digits with the kurus behind a point: '100', '100.1'
         * or '100.10'. A comma is refused. It is a string so that it is
         * signed and sent exactly as it is written here, with no rounding of
         * its own on the way.
         */
        public string $amount,
        public int $installmentNumber,
        /** The address the customer is paying from, as the merchant sees it. */
        public string $ip,
        public Customer $customer,
        /** The card typed in. Left out only when a kept card is named instead. */
        public ?Card $card = null,
        /** A card the customer let the merchant keep, by the number the gateway gave it. */
        public ?int $savedCardId = null,
        /** Three letters, e.g. TRY. Left out, the gateway takes the lira. */
        public ?string $currency = null,
        /** The payment account to charge through. Left out, the team's default account is used. */
        public ?int $paymentProviderId = null,
        /**
         * What is being sold, where the customer spreads the amount over
         * months and the bank takes something for the waiting on top of it.
         * Left out where the two are the same, which is most payments.
         */
        public ?string $baseAmount = null,
        ?int $channelId = null,
    ) {
        if (($this->card === null) === ($this->savedCardId === null)) {
            throw new InvalidArgumentException('Bir ödeme ya bir kartla ya da kayıtlı bir kartla yapılır; ikisi birden ya da hiçbiri verilemez.');
        }

        parent::__construct($channelId);
    }

    /**
     * The request body, in the snake_case the gateway speaks. The signature
     * is not part of it; the client signs the body as a whole and sends the
     * signature in a header of its own.
     *
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        $body = [
            'transaction' => self::said([
                'channel_id' => $this->channel($channelId),
                'channel_reference' => $this->channelReference,
                'payment_provider_id' => $this->paymentProviderId,
                'amount' => $this->amount,
                ...($this->baseAmount === null ? [] : ['base_amount' => $this->baseAmount]),
                'currency' => $this->currency,
                'installment_number' => $this->installmentNumber,
                'ip' => $this->ip,
                'saved_card_id' => $this->savedCardId,
            ]),
            'customer' => $this->customer->toArray(),
        ];

        return $this->card === null ? $body : [...$body, 'card' => $this->card->toArray()];
    }
}
