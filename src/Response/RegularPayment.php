<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The outcome of a payment charged straight to the card. A successful one
 * is settled; there is nowhere left to send the customer.
 */
final readonly class RegularPayment extends Payment {}
