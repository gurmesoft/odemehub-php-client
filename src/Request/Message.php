<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Something handed to the gateway. Everything sent there is plain JSON,
 * signed as a whole by the client, so what is common to all of them is the
 * endpoint it goes to and the body it is sent as.
 *
 * The body is built with the client's channel handed in, because a message
 * that speaks for a channel puts it where its own endpoint expects it; one
 * that does not, such as a refund, simply never reads it.
 */
abstract readonly class Message
{
    /**
     * The endpoint this is sent to, under the team's gateway.
     */
    abstract public function path(): string;

    /**
     * The request body, in the snake_case the gateway speaks.
     *
     * @param  int  $channelId  The client's channel, for the messages that speak for one.
     * @return array<string, mixed>
     */
    abstract public function toArray(int $channelId): array;

    /**
     * Drop what the caller left unsaid, so an optional field is left out of
     * the body altogether rather than sent empty.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    protected static function said(array $body): array
    {
        return array_filter($body, static fn (mixed $value): bool => $value !== null);
    }
}
