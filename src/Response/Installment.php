<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * One way of paying an amount off on a card: how many months it is spread
 * over, what the customer pays each month, and what the card is charged in
 * all. A single payment is one instalment whose total is the amount.
 */
final readonly class Installment
{
    public function __construct(
        public int $number,
        /** What is charged each month, as digits with the kurus behind a point. */
        public string $amount,
        /** What the card is charged in all, the same way. */
        public string $total,
    ) {}

    /**
     * @param  array<string, mixed>  $installment
     */
    public static function fromArray(array $installment): self
    {
        return new self(
            number: (int) ($installment['number'] ?? 0),
            amount: (string) ($installment['amount'] ?? ''),
            total: (string) ($installment['total'] ?? ''),
        );
    }
}
