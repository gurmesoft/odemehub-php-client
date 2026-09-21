<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * An order opened to be paid on the gateway's own page. Nothing is charged
 * here: the answer carries the address to send the customer to, and they
 * give their card there. The customer is given whole, because that page
 * asks them for nothing but the card.
 */
final readonly class Checkout extends ChannelMessage
{
    /**
     * @param  list<OrderItem>  $items  What the order is made up of, to be shown to the customer.
     */
    public function __construct(
        /** The number the order is known by in the calling system. */
        public string $channelReference,
        /** What the customer pays, as digits with the kurus behind a point. */
        public string $amount,
        /** Where the customer is posted back to, with the signed outcome, once the order is paid. */
        public string $successUrl,
        public Customer $customer,
        /** Where the customer goes if they turn back without paying. */
        public ?string $cancelUrl = null,
        public ?string $description = null,
        /** What is owed before tax. */
        public ?string $subtotal = null,
        /** The tax on the order. */
        public ?string $taxAmount = null,
        /** Three letters, e.g. TRY. Left out, the gateway takes the lira. */
        public ?string $currency = null,
        public array $items = [],
        ?int $channelId = null,
    ) {
        parent::__construct($channelId);
    }

    public function path(): string
    {
        return 'checkout-payment';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        return [
            'order' => self::said([
                'channel_id' => $this->channel($channelId),
                'channel_reference' => $this->channelReference,
                'description' => $this->description,
                'amount' => $this->amount,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'currency' => $this->currency,
                'success_url' => $this->successUrl,
                'cancel_url' => $this->cancelUrl,
                'items' => $this->items === [] ? null : array_map(
                    static fn (OrderItem $item): array => $item->toArray(),
                    $this->items,
                ),
            ]),
            'customer' => $this->customer->toArray(),
        ];
    }
}
