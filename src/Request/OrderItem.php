<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * One line of what an order is made up of. The lines are shown to the
 * customer on the checkout page; what is owed is the order's own amount,
 * so a line is never what the payment is taken from.
 */
final readonly class OrderItem
{
    public function __construct(
        public string $name,
        public int $quantity,
        /** The price of one, as digits with the kurus behind a point. */
        public string $unitAmount,
        /** The tax on the line as a percentage, e.g. '20'. */
        public ?string $taxRate = null,
        /** What the tax on the whole line comes to. */
        public ?string $taxAmount = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit_amount' => $this->unitAmount,
            'tax_rate' => $this->taxRate,
            'tax_amount' => $this->taxAmount,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
