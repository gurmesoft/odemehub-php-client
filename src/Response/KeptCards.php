<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The cards a customer has let the merchant keep. The card they pay with
 * unless they say otherwise comes first.
 */
final readonly class KeptCards
{
    /**
     * @param  list<SavedCard>  $savedCards
     */
    public function __construct(
        public Result $result,
        public array $savedCards,
        /** The merchant's own key for the customer the cards belong to. */
        public string $customerChannelReference,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $cards = is_array($body['saved_cards'] ?? null) ? $body['saved_cards'] : [];
        $customer = is_array($body['customer'] ?? null) ? $body['customer'] : [];

        return new self(
            result: Result::fromArray($body),
            savedCards: array_values(array_map(
                static fn (mixed $card): SavedCard => SavedCard::fromArray(is_array($card) ? $card : []),
                $cards,
            )),
            customerChannelReference: (string) ($customer['channel_reference'] ?? ''),
        );
    }
}
