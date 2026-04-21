<?php

declare(strict_types=1);

namespace App\Demo;

final class PriceFormatter
{
    public function format(int $cents): string
    {
        $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($cents / 100, 'EUR');
    }
}
