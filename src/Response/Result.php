<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * How a request went, as every answer opens: whether it worked and, only
 * when it did not, what went wrong. Something that worked has nothing to
 * say beyond that it did.
 */
final readonly class Result
{
    public function __construct(
        public bool $successful,
        public ?string $message,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $result = is_array($body['result'] ?? null) ? $body['result'] : [];
        $message = $result['message'] ?? null;

        return new self(
            successful: (bool) ($result['successful'] ?? false),
            message: is_string($message) && $message !== '' ? $message : null,
        );
    }
}
