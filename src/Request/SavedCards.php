<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The cards a customer has let the merchant keep, asked for by naming the
 * customer. The card that is theirs by default comes first.
 */
final readonly class SavedCards extends ChannelMessage
{
    public function __construct(
        public NamedCustomer $customer,
        ?int $channelId = null,
    ) {
        parent::__construct($channelId);
    }

    public function path(): string
    {
        return 'saved-cards';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        return ['customer' => $this->customer->toArray($this->channel($channelId))];
    }
}
