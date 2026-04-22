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

    // Bindée à l'input côté client : chaque frappe met à jour $query sur le serveur
    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    /**
     * Recalculée à chaque rendu : pas besoin d'une LiveAction dédiée pour filtrer.
     *
     * @return list<Product>
     */
    public function getResults(): array
    {
        return mb_strlen(trim($this->query)) >= 2
            ? $this->products->search($this->query)
            : [];
    }
}
