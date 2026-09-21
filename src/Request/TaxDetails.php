<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Who a customer is billed as when they buy for a company. The three are
 * always given together: a company is no use on an invoice half given, and
 * the gateway turns down a customer that names one of them without the
 * others.
 */
final readonly class TaxDetails
{
    public function __construct(
        public string $companyTitle,
        public string $taxNumber,
        public string $taxOffice,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'company_title' => $this->companyTitle,
            'tax_number' => $this->taxNumber,
            'tax_office' => $this->taxOffice,
        ];
    }
}
