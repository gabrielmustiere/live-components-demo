<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Demo\PriceFormatter;
use App\Demo\Product;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ProductCard
{
    public Product $product;

    public bool $showBadge = true;

    public function __construct(
        private readonly PriceFormatter $formatter,
    ) {
    }

    public function formattedPrice(): string
    {
        return $this->formatter->format($this->product->price);
    }
}
