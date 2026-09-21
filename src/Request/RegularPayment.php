<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A payment charged straight to the card, without sending the customer to
 * their bank to confirm it. A successful answer is a settled payment.
 */
final readonly class RegularPayment extends Payment
{
    public function path(): string
    {
        return 'regular-payment';
    }
}
