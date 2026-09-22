<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * How a payment went, asked for after the fact. A customer sent to their
 * bank comes back carrying the payment's number and nothing more, because
 * a browser cannot be given anything to sign with; this is the call that
 * says what became of it.
 */
final readonly class RetrievePayment extends PaymentMessage
{
    public function path(): string
    {
        return 'payment';
    }
}
