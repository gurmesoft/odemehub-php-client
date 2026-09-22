<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Where a subscription stands: what it is for, the period it is on and
 * whether that period has been paid for. Nothing is changed by asking.
 */
final readonly class RetrieveSubscription extends SubscriptionMessage
{
    public function path(): string
    {
        return 'subscription';
    }
}
