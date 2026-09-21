<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The answer to something done to one card: keeping it, letting it go, or
 * making it the customer's default. What came back of the card is the card
 * as it now stands; a card that was let go of comes back carrying only the
 * number it had, since there is nothing left of it to show.
 */
final readonly class KeptCard
{
    public function __construct(
        public Result $result,
        /** The card as it now stands, or nothing when there was none to keep. */
        public ?SavedCard $savedCard,
        /** The merchant's own key for the customer the card belongs to. */
        public string $customerChannelReference,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $savedCard = $body['saved_card'] ?? null;
        $customer = is_array($body['customer'] ?? null) ? $body['customer'] : [];

        return new self(
            result: Result::fromArray($body),
            savedCard: is_array($savedCard) ? SavedCard::fromArray($savedCard) : null,
            customerChannelReference: (string) ($customer['channel_reference'] ?? ''),
        );
    }
}
