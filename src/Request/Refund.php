<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Money given back out of a payment the provider has already settled, whole
 * or in part.
 */
final readonly class Refund extends GiveBack
{
    public function __construct(
        int $transactionId,
        /**
         * How much goes back, as digits with the kurus behind a point:
         * '35.50'. Leave it out and everything the payment has left in it
         * goes back, which is the whole of it until part of it has already
         * been given back. It is never more than the payment has left: the
         * gateway turns down anything larger.
         */
        public ?string $amount = null,
    ) {
        parent::__construct($transactionId);
    }

    public function path(): string
    {
        return 'refund-payment';
    }

    /**
     * The body. An amount that was not named is left out of the request
     * altogether rather than sent empty.
     *
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        return self::said([
            ...parent::toArray($channelId),
            'amount' => $this->amount,
        ]);
    }
}
