<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Money asked back out of a payment already made. The payment is named by
 * the number the gateway gave it when it was made, and nothing else about
 * it is sent: the gateway holds the account, the provider, the channel and
 * the reference the provider knows the payment by.
 */
abstract readonly class GiveBack extends Message
{
    public function __construct(
        /** The payment's number in the gateway, as it answered when the payment was made. */
        public int $transactionId,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        return ['transaction_id' => $this->transactionId];
    }
}
