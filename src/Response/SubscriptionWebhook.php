<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * A word the gateway sent about one of the merchant's subscriptions. It
 * arrives at the address the subscription was opened with, as plain JSON
 * signed in the `X-Signature` header, and is not believed until that
 * signature is checked.
 *
 * What is said is the state the subscription has reached, not the thing
 * that was done to it: a first payment and a renewal both say `active`.
 * Being called off (`cancelled`) and running out (`ended`) are two
 * moments, because a subscription called off today still serves the days
 * it has been paid for.
 */
final readonly class SubscriptionWebhook
{
    public function __construct(
        /** The state reached: active, past_due, cancelled or ended. */
        public string $event,
        /** The subscription as it stands now. */
        public Subscription $subscription,
    ) {}

    /**
     * Whether the subscription is being paid for: the customer has just
     * paid a period, whether the first or a later one.
     */
    public function isActive(): bool
    {
        return $this->event === 'active';
    }

    /**
     * Whether a period was left unpaid. The card was tried and turned away
     * every time, and the customer has been asked to pay it themselves at
     * the subscription's `checkoutUrl`.
     */
    public function isPastDue(): bool
    {
        return $this->event === 'past_due';
    }

    /**
     * Whether the subscription has been called off. Nothing more will be
     * charged, but the customer is served until `endsAt`.
     */
    public function isCancelled(): bool
    {
        return $this->event === 'cancelled';
    }

    /**
     * Whether it is over: the days that were paid for have run out and the
     * customer's access can be closed.
     */
    public function isEnded(): bool
    {
        return $this->event === 'ended';
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            event: (string) ($body['event'] ?? ''),
            subscription: Subscription::fromArray($body),
        );
    }
}
