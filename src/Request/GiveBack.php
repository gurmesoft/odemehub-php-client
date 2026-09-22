<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Money asked back out of a payment already made, whole or in part.
 */
abstract readonly class GiveBack extends PaymentMessage {}
