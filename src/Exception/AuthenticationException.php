<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

/**
 * The gateway did not accept the credentials: either the API key is not the
 * one issued to the team in the address, or the request was not signed with
 * the matching secret.
 */
class AuthenticationException extends OdemehubException
{
    //
}
