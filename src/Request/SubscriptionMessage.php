<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Something asked of a subscription that has already been opened. The
 * subscription is named by the number the gateway gave it, and nothing
 * else is sent: the gateway holds the product, the customer, the channel
 * and the periods it has been through.
 */
abstract readonly class SubscriptionMessage extends Message
{
    public function __construct(
        /** The subscription's number in the gateway, as it answered when it was opened. */
        public int $subscriptionId,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        return ['subscription_id' => $this->subscriptionId];
    }
}
