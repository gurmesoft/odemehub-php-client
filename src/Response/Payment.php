<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The outcome of a payment, as the gateway reports it — whether it answers
 * straight away or posts the outcome back once the customer is home from
 * their bank. The two are the same shape, so a merchant reads them the same
 * way: how it went, which payment it was, and whose.
 *
 * A payment that was turned down is an outcome like any other and arrives
 * here; only answers that were never a payment outcome are raised as
 * exceptions.
 */
readonly class Payment
{
    public function __construct(
        public Result $result,
        /** The payment's number in the gateway, which names it again for a refund. */
        public int $transactionId,
        /** The channel the payment came in on. */
        public int $channelId,
        /** The number the order is known by in the calling system. */
        public string $channelReference,
        /** The merchant's own key for the customer the payment was made for. */
        public string $customerChannelReference,
        /**
         * The card the payment kept, for a payment that asked for one to be
         * kept. It is null while nothing was kept: because the payment did
         * not go through, because the provider handed nothing back, or
         * because the payment never asked.
         */
        public ?SavedCard $savedCard = null,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): static
    {
        return new static(...static::parts($body));
    }

    /**
     * The pieces every payment outcome is read out of, named as the
     * constructor takes them, so a kind of payment that says more can add
     * to them rather than read the body again.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    protected static function parts(array $body): array
    {
        $transaction = is_array($body['transaction'] ?? null) ? $body['transaction'] : [];
        $customer = is_array($body['customer'] ?? null) ? $body['customer'] : [];
        $savedCard = $body['saved_card'] ?? null;

        return [
            'result' => Result::fromArray($body),
            'transactionId' => (int) ($transaction['id'] ?? 0),
            'channelId' => (int) ($transaction['channel_id'] ?? 0),
            'channelReference' => (string) ($transaction['channel_reference'] ?? ''),
            'customerChannelReference' => (string) ($customer['channel_reference'] ?? ''),
            'savedCard' => is_array($savedCard) ? SavedCard::fromArray($savedCard) : null,
        ];
    }
}
