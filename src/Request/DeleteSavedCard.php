<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Letting go of a kept card. It is dropped at the provider first and with
 * the gateway after: a card the provider would not let go of stays, and the
 * answer says why.
 */
final readonly class DeleteSavedCard extends SavedCardMessage
{
    public function path(): string
    {
        return 'delete-saved-card';
    }
}
