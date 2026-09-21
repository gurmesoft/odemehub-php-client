<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The customer a payment is made for, an order is opened for or a card is
 * kept for. The merchant names them by its own key for them on the channel
 * they came in on: the same key twice is the same customer, and what is
 * said of them here becomes the latest the gateway knows.
 */
final readonly class Customer
{
    public function __construct(
        /** The key the merchant keeps this customer under in its own system. */
        public string $channelReference,
        public string $firstname,
        public string $lastname,
        public string $email,
        public string $phone,
        public string $address,
        public string $district,
        public string $province,
        public string $country,
        /** The company they are billed as, for a customer buying for one. */
        public ?TaxDetails $tax = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $customer = [
            'channel_reference' => $this->channelReference,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'district' => $this->district,
            'province' => $this->province,
            'country' => $this->country,
        ];

        return $this->tax === null ? $customer : [...$customer, 'tax' => $this->tax->toArray()];
    }
}
