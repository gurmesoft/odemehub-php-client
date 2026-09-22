<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A subscription called off. Nothing is given back: the customer keeps the
 * days they have already paid for and is served to the end of them, and
 * nothing is charged after that.
 */
final readonly class CancelSubscription extends SubscriptionMessage
{
    public function path(): string
    {
        return 'cancel-subscription';
    }
}
