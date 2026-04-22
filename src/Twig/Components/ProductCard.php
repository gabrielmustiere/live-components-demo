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

    // Les components sont autowirés : on peut injecter n'importe quel service
    public function __construct(
        private readonly PriceFormatter $formatter,
    ) {
    }

    // Méthode publique accessible dans le template via `this.formattedPrice`
    public function formattedPrice(): string
    {
        return $this->formatter->format($this->product->price);
    }
}
