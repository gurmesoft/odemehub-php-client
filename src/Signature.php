<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub;

/**
 * How a merchant and the gateway vouch for each other's bodies. A body
 * travels as plain JSON and, next to it in the `X-Signature` header, an
 * HMAC-SHA256 of that exact text under the merchant's secret. The secret
 * itself never travels; a body whose signature does not match was not
 * written by the holder of the secret, or was changed on the way.
 *
 * This is the same calculation the application makes in its own
 * `Services\Gateway\Signer`. Nothing is layered on top, so a body can be
 * signed and checked by hand:
 *
 *     hash_hmac('sha256', $body, $apiSecret)
 *
 * Test vector: with the secret `secret_test` the body `{"a":1}` is signed
 * `6d0c951564cdd2b6b70e75b214293a8cd2542815ba54fe91c7f6ce105bc3d592`.
 */
final readonly class Signature
{
    public const ALGORITHM = 'sha256';

    /**
     * The header both the request and the answer carry the signature in.
     */
    public const HEADER = 'X-Signature';

    public function __construct(private string $apiSecret) {}

    /**
     * The signature that vouches for a body: HMAC-SHA256 over the exact
     * text, written as lowercase hex.
     */
    public function sign(string $body): string
    {
        return hash_hmac(self::ALGORITHM, $body, $this->apiSecret);
    }

    /**
     * Whether a signature vouches for a body.
     */
    public function verify(string $body, ?string $signature): bool
    {
        return is_string($signature) && hash_equals($this->sign($body), $signature);
    }
}
