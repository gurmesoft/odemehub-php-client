<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A customer the gateway already knows, named and nothing more: the channel
 * they came in on and the merchant's own key for them there. It is what the
 * endpoints that only look a customer up take, such as listing the cards
 * kept for them; a customer never seen before is not made by naming them.
 */
final readonly class NamedCustomer
{
    public function __construct(
        /** The key the merchant keeps this customer under in its own system. */
        public string $channelReference,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        return [
            'channel_id' => $channelId,
            'channel_reference' => $this->channelReference,
        ];
    }
}
