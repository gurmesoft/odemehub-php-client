<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * A subscription as the gateway keeps it: what is subscribed to, where it
 * stands, and the period it is on. The same answer comes back whether the
 * subscription has just been opened, asked after or called off.
 *
 * The amount is the price of the period it is on, not of the product as it
 * is priced today: a merchant that puts its price up is paid the new price
 * from the next period on, and the one being served stays as it was
 * charged.
 */
final readonly class Subscription
{
    public function __construct(
        public Result $result,
        /** The subscription's number in the gateway; name it here to ask after it later. */
        public int $id,
        /** The channel the subscription was opened on. */
        public int $channelId,
        /** The key the subscription is known by in the calling system. */
        public string $channelReference,
        /** The recurring product being subscribed to. */
        public int $productId,
        public string $productName,
        /** Where it stands: pending, active, past_due or cancelled. */
        public string $status,
        /** How often a period comes round: monthly or yearly. */
        public string $period,
        /** What the period it is on costs, with the kurus behind a point. */
        public string $amount,
        public string $currency,
        /** When the period it is on began, once it has been paid for. */
        public ?string $startsAt,
        /** When the period it is on runs out, which is when the next is charged. */
        public ?string $endsAt,
        /** When the period it is on was paid for, if it has been. */
        public ?string $paidAt,
        /** The day it was called off on, if it has been. */
        public ?string $cancelledAt,
        /** Where the customer pays the period it is on, while that is still owed. */
        public ?string $checkoutUrl,
        /** Whether it was paid for in the test environment; nothing until the first payment. */
        public ?bool $isTest,
        /** The merchant's own key for the customer, answered when the subscription is opened. */
        public ?string $customerChannelReference,
    ) {}

    /**
     * Whether the subscription is being paid for: a customer who has been
     * through the checkout and whose card has not since been turned away.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Whether the first period has yet to be paid for. A subscription stays
     * here until the customer has been through the checkout.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Whether a period has been left unpaid: the card was tried and turned
     * away every time, and the customer has been asked to pay it
     * themselves at `checkoutUrl`.
     */
    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Whether it is over. A subscription that has been called off but is
     * still serving days that were paid for is not over yet — read
     * `cancelledAt` for that.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $subscription = is_array($body['subscription'] ?? null) ? $body['subscription'] : [];
        $customer = is_array($body['customer'] ?? null) ? $body['customer'] : [];

        return new self(
            result: Result::fromArray($body),
            id: (int) ($subscription['id'] ?? 0),
            channelId: (int) ($subscription['channel_id'] ?? 0),
            channelReference: (string) ($subscription['channel_reference'] ?? ''),
            productId: (int) ($subscription['product_id'] ?? 0),
            productName: (string) ($subscription['product_name'] ?? ''),
            status: (string) ($subscription['status'] ?? ''),
            period: (string) ($subscription['period'] ?? ''),
            amount: (string) ($subscription['amount'] ?? ''),
            currency: (string) ($subscription['currency'] ?? ''),
            startsAt: self::said($subscription['starts_at'] ?? null),
            endsAt: self::said($subscription['ends_at'] ?? null),
            paidAt: self::said($subscription['paid_at'] ?? null),
            cancelledAt: self::said($subscription['cancelled_at'] ?? null),
            checkoutUrl: self::said($subscription['checkout_url'] ?? null),
            isTest: isset($subscription['is_test']) ? (bool) $subscription['is_test'] : null,
            customerChannelReference: self::said($customer['channel_reference'] ?? null),
        );
    }

    /**
     * A field the gateway left empty reads as nothing rather than as an
     * empty string, so there is one way of asking whether it was said.
     */
    private static function said(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
