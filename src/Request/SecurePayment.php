<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A payment the customer confirms with their bank. The gateway does not
 * settle it; it hands back the address the customer has to be sent to, and
 * posts them back to the address named here once they are done.
 */
final readonly class SecurePayment extends Payment
{
    public function __construct(
        int $channelReference,
        string $amount,
        int $installmentNumber,
        string $ip,
        Customer $customer,
        /** Where the customer is posted back to, with the signed outcome, once they are done at their bank. */
        public string $callbackUrl,
        ?Card $card = null,
        ?int $savedCardId = null,
        ?string $currency = null,
        ?int $paymentProviderId = null,
        ?int $channelId = null,
    ) {
        parent::__construct(
            $channelReference,
            $amount,
            $installmentNumber,
            $ip,
            $customer,
            $card,
            $savedCardId,
            $currency,
            $paymentProviderId,
            $channelId,
        );
    }

    public function path(): string
    {
        return 'secure-payment';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(int $channelId): array
    {
        $body = parent::toArray($channelId);
        $body['transaction']['callback_url'] = $this->callbackUrl;

        return $body;
    }
}
