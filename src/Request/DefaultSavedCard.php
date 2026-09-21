<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Making one of a customer's kept cards the one they pay with unless they
 * say otherwise. A customer has one such card; the one that was it before
 * stops being it as this one is written down.
 */
final readonly class DefaultSavedCard extends SavedCardMessage
{
    public function path(): string
    {
        return 'default-saved-card';
    }
}
