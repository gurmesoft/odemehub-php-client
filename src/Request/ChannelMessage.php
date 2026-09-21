<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A message that speaks for one of the team's channels: a payment, an order
 * opened for the checkout, a card kept for a customer. The channel belongs
 * to the integration rather than to any one message, so it is named once on
 * the client; a merchant selling on more than one channel names another
 * here, on the single message that belongs elsewhere.
 */
abstract readonly class ChannelMessage extends Message
{
    public function __construct(
        /** The channel this one message speaks for. Left out, the client's own is used. */
        public ?int $channelId = null,
    ) {}

    /**
     * The channel this message is for: the one it names, or the client's.
     */
    protected function channel(int $channelId): int
    {
        return $this->channelId ?? $channelId;
    }
}
