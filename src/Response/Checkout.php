<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The order opened to be paid on the gateway's own page. Nothing has been
 * charged yet: the customer has to be sent to the address here, and what
 * becomes of the order is posted back to the merchant afterwards, the same
 * way a payment's outcome is.
 */
final readonly class Checkout
{
    public function __construct(
        public Result $result,
        /** The order's number in the gateway. */
        public int $id,
        /** The channel the order was opened on. */
        public int $channelId,
        /** The number the order is known by in the calling system. */
        public string $channelReference,
        public string $amount,
        public string $currency,
        /** Where the order stands: open until it is paid. */
        public string $status,
        /** Where the customer has to be sent to pay. */
        public string $checkoutUrl,
        /** The merchant's own key for the customer the order is for. */
        public string $customerChannelReference,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $order = is_array($body['order'] ?? null) ? $body['order'] : [];
        $customer = is_array($body['customer'] ?? null) ? $body['customer'] : [];

        return new self(
            result: Result::fromArray($body),
            id: (int) ($order['id'] ?? 0),
            channelId: (int) ($order['channel_id'] ?? 0),
            channelReference: (string) ($order['channel_reference'] ?? ''),
            amount: (string) ($order['amount'] ?? ''),
            currency: (string) ($order['currency'] ?? ''),
            status: (string) ($order['status'] ?? ''),
            checkoutUrl: (string) ($order['checkout_url'] ?? ''),
            customerChannelReference: (string) ($customer['channel_reference'] ?? ''),
        );
    }
}
