<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A subscription opened for a customer, to be paid for the first time on
 * the gateway's own page. Nothing is charged here: the answer carries the
 * address to send the customer to, and they give their card there. The
 * card is kept, because the periods to come are taken from it.
 *
 * What is subscribed to is one of the merchant's own recurring products,
 * named by its number in the gateway; the price and how often it comes
 * round are the product's, so nothing of the sort is sent here.
 */
final readonly class Subscription extends ChannelMessage
{
    public function __construct(
        /** The recurring product being subscribed to, by its number in the gateway. */
        public int $productId,
        /** The key the subscription is known by in the calling system. */
        public string $channelReference,
        /** Where the customer is posted back to, with the signed outcome, once the first period is paid. */
        public string $successUrl,
        public Customer $customer,
        /** Where the customer goes if they turn back without paying. */
        public ?string $cancelUrl = null,
        /** Where this merchant is told, signed, whenever the subscription's state changes. */
        public ?string $webhookUrl = null,
        ?int $channelId = null,
    ) {
        parent::__construct($channelId);
    }

    public function path(): string
    {
        return 'subscription-payment';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        return [
            'subscription' => self::said([
                'channel_id' => $this->channel($channelId),
                'channel_reference' => $this->channelReference,
                'product_id' => $this->productId,
                'success_url' => $this->successUrl,
                'cancel_url' => $this->cancelUrl,
                'webhook_url' => $this->webhookUrl,
            ]),
            'customer' => $this->customer->toArray(),
        ];
    }
}
