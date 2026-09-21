<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The whole of a payment taken back before the provider has settled it.
 * There is no amount to name: a cancellation is always for the whole of the
 * payment, and anything less goes back as a refund.
 */
final readonly class Cancel extends GiveBack
{
    public function path(): string
    {
        return 'cancel-payment';
    }
}
