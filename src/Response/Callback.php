<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The outcome the gateway posts back to the merchant once the customer has
 * been through their bank. It arrives at the merchant's own address as a
 * form post carrying two fields: `data`, the outcome as the very JSON text
 * the gateway signed, and `hash`, its signature.
 *
 * The outcome inside is the same shape a payment answers with, so a
 * merchant reads a payment it started and a customer coming home from the
 * bank the same way.
 */
final readonly class Callback extends Payment
{
    /**
     * The field the outcome travels in, as the text that was signed.
     */
    public const PAYLOAD_FIELD = 'data';

    /**
     * The field the signature travels in.
     */
    public const SIGNATURE_FIELD = 'hash';
}
