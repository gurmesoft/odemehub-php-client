<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

/**
 * The request reached the gateway and was signed correctly, but its contents
 * were refused. No payment was attempted.
 */
class ValidationException extends OdemehubException
{
    /**
     * @param  array<string, list<string>>  $errors  The refused fields, each with the reasons it was refused.
     */
    public function __construct(
        string $message,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }
}
