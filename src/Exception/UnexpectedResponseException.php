<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

/**
 * The gateway answered with something that is neither a payment outcome nor
 * a refusal this client knows how to read.
 */
class UnexpectedResponseException extends OdemehubException
{
    public function __construct(
        string $message,
        public readonly int $status,
    ) {
        parent::__construct($message);
    }
}
