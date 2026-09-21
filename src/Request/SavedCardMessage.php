<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Something done to one of a customer's kept cards. The card is named by
 * the number the gateway gave it, and the customer alongside it, so a card
 * can only ever be reached through the customer it belongs to.
 */
abstract readonly class SavedCardMessage extends ChannelMessage
{
    public function __construct(
        public NamedCustomer $customer,
        /** The card's number in the gateway, as a listing of the customer's cards gave it. */
        public int $savedCardId,
        ?int $channelId = null,
    ) {
        parent::__construct($channelId);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        return [
            'customer' => $this->customer->toArray($this->channel($channelId)),
            'saved_card' => ['id' => $this->savedCardId],
        ];
    }
}
