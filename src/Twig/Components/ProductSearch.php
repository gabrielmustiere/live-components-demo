<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Demo\Product;
use App\Demo\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProductSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    /**
     * @return list<Product>
     */
    public function getResults(): array
    {
        return mb_strlen(trim($this->query)) >= 2
            ? $this->products->search($this->query)
            : [];
    }
}
