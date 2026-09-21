<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The outcome of starting a payment the customer confirms with their bank.
 * The form the bank wants is not handed over: the gateway keeps it and
 * serves it itself, so all that comes back is the address to send the
 * customer to. Until they have been there and come back, the payment has
 * not been made.
 */
final readonly class SecurePayment extends Payment
{
    public function __construct(
        Result $result,
        int $transactionId,
        int $channelId,
        string $channelReference,
        string $customerChannelReference,
        /** Where the customer has to be sent. Always there when the payment started. */
        public ?string $redirectUrl = null,
        ?SavedCard $savedCard = null,
    ) {
        parent::__construct($result, $transactionId, $channelId, $channelReference, $customerChannelReference, $savedCard);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): static
    {
        $result = is_array($body['result'] ?? null) ? $body['result'] : [];
        $redirectUrl = $result['redirect_url'] ?? null;

        return new static(
            ...static::parts($body),
            redirectUrl: is_string($redirectUrl) && $redirectUrl !== '' ? $redirectUrl : null,
        );
    }
}
