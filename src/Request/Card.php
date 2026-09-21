<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The card a payment is attempted with. The number and the security code
 * live here and travel no further than the request body: the gateway keeps
 * only the head and the tail digits of the number and no digit of the code.
 */
final readonly class Card
{
    public function __construct(
        public string $holderName,
        /** The number, digits only, without spaces. */
        public string $number,
        public string $securityCode,
        /** Two digits, e.g. 04. */
        public string $expiryMonth,
        /** Four digits, e.g. 2030. */
        public string $expiryYear,
        /**
         * Whether the customer asked for this card to be kept, so they can
         * pay with it again without typing it out. The account's provider
         * has to be able to charge a kept card; one that cannot turns the
         * payment down on this field rather than declining it.
         */
        public bool $shouldSave = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'holder_name' => $this->holderName,
            'number' => $this->number,
            'security_code' => $this->securityCode,
            'expiry_month' => $this->expiryMonth,
            'expiry_year' => $this->expiryYear,
            'should_save' => $this->shouldSave,
        ];
    }

    /**
     * Keep the number and the security code out of dumps, stack traces and
     * anything else that reads an object's properties for display.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'holderName' => $this->holderName,
            'number' => str_repeat('*', max(strlen($this->number) - 4, 0)).substr($this->number, -4),
            'securityCode' => str_repeat('*', max(strlen($this->securityCode) - 1, 0)).substr($this->securityCode, -1),
            'expiryMonth' => $this->expiryMonth,
            'expiryYear' => $this->expiryYear,
            'shouldSave' => $this->shouldSave,
        ];
    }
}
