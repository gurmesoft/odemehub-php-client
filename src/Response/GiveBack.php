<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * What became of money asked back out of a payment. An attempt the provider
 * turned down is an outcome like any other and arrives here; only answers
 * that were never an outcome at all are raised as exceptions.
 *
 * A cancellation and a refund answer the same way, so both come back as
 * this. What actually went back is answered along with the rest, because a
 * refund may have been asked for without naming an amount.
 */
final readonly class GiveBack extends Payment
{
    public function __construct(
        Result $result,
        int $transactionId,
        int $channelId,
        string $channelReference,
        string $customerChannelReference,
        /** Which of the two it was: a cancellation or a refund. */
        public string $type = '',
        /** How much actually went back, whether or not it was asked for by name. */
        public ?string $amount = null,
        ?SavedCard $savedCard = null,
    ) {
        parent::__construct($result, $transactionId, $channelId, $channelReference, $customerChannelReference, $savedCard);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): static
    {
        $refund = is_array($body['refund'] ?? null) ? $body['refund'] : [];
        $amount = $refund['amount'] ?? null;

        return new static(
            ...static::parts($body),
            type: (string) ($refund['type'] ?? ''),
            amount: $amount === null ? null : (string) $amount,
        );
    }
}
