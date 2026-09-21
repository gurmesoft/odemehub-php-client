<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

use RuntimeException;

/**
 * Base class for everything this client raises, so a caller that does not
 * care which way a payment failed can catch one thing.
 */
class OdemehubException extends RuntimeException
{
    //
}
