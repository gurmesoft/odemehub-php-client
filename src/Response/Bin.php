<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * What is known about a card from the head of its number. A gateway that
 * could not find out leaves the fields unsaid rather than guessing, so a
 * merchant asks the result whether the answer is worth reading before it
 * draws anything from it.
 */
final readonly class Bin
{
    /**
     * @param  list<Installment>  $installments  The ways the amount may be paid off, a single payment first.
     */
    public function __construct(
        public Result $result,
        /** The digits the question was asked with. */
        public string $bin,
        /** The institution that issued the card. */
        public ?string $issuerName,
        public ?string $issuerCode,
        /** The scheme the card is issued on, e.g. visa, as the issuer reports it. */
        public ?string $scheme,
        /** Whether the money is lent, drawn from an account or loaded beforehand: credit, debit or prepaid. */
        public ?string $type,
        /** The programme the card is sold under, such as Bonus or Maximum. */
        public ?string $program,
        /** Whether the card belongs to a company rather than to a person. */
        public ?bool $isCommercial,
        public array $installments,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $card = is_array($body['card'] ?? null) ? $body['card'] : [];
        $installments = is_array($body['installments'] ?? null) ? $body['installments'] : [];

        return new self(
            result: Result::fromArray($body),
            bin: (string) ($card['bin'] ?? ''),
            issuerName: isset($card['issuer_name']) ? (string) $card['issuer_name'] : null,
            issuerCode: isset($card['issuer_code']) ? (string) $card['issuer_code'] : null,
            scheme: isset($card['scheme']) ? (string) $card['scheme'] : null,
            type: isset($card['type']) ? (string) $card['type'] : null,
            program: isset($card['program']) ? (string) $card['program'] : null,
            isCommercial: isset($card['is_commercial']) ? (bool) $card['is_commercial'] : null,
            installments: array_values(array_map(
                static fn (mixed $installment): Installment => Installment::fromArray(
                    is_array($installment) ? $installment : [],
                ),
                $installments,
            )),
        );
    }
}
