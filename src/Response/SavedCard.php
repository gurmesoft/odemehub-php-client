<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * A card a customer let the merchant keep, as the gateway shows it: enough
 * to draw the card and to name it again, and nothing that could charge it.
 * What lets it be charged again stays with the gateway.
 */
final readonly class SavedCard
{
    public function __construct(
        /** The card's number in the gateway, which names it again later. */
        public int $id,
        /** The account the card is kept at; it can only be charged there. */
        public ?int $paymentProviderId,
        public string $holderName,
        /** The network the card belongs to, e.g. visa, as far as it is known. */
        public ?string $scheme,
        public string $firstEightDigit,
        public string $lastFourDigit,
        public string $expiryMonth,
        public string $expiryYear,
        /** Whether this is the card the customer pays with unless they say otherwise. */
        public bool $isDefault,
        public ?string $createdAt,
    ) {}

    /**
     * @param  array<string, mixed>  $card
     */
    public static function fromArray(array $card): self
    {
        return new self(
            id: (int) ($card['id'] ?? 0),
            paymentProviderId: isset($card['payment_provider_id']) ? (int) $card['payment_provider_id'] : null,
            holderName: (string) ($card['holder_name'] ?? ''),
            scheme: isset($card['scheme']) ? (string) $card['scheme'] : null,
            firstEightDigit: (string) ($card['first_eight_digit'] ?? ''),
            lastFourDigit: (string) ($card['last_four_digit'] ?? ''),
            expiryMonth: (string) ($card['expiry_month'] ?? ''),
            expiryYear: (string) ($card['expiry_year'] ?? ''),
            isDefault: (bool) ($card['is_default'] ?? false),
            createdAt: isset($card['created_at']) ? (string) $card['created_at'] : null,
        );
    }
}
