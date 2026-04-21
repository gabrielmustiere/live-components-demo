<?php

declare(strict_types=1);

namespace App\Demo;

final class ProductRepository
{
    /**
     * @return list<Product>
     */
    public function all(): array
    {
        return [
            new Product(name: 'Clavier mécanique Keychron K2', price: 12900, new: true),
            new Product(name: 'Casque ANC Sony WH-1000XM5', price: 39900),
            new Product(name: 'Webcam Logitech Brio 4K', price: 19900),
            new Product(name: 'Écran 27" Dell U2723QE', price: 64900, new: true),
            new Product(name: 'Souris MX Master 3S', price: 11900),
            new Product(name: 'Microphone Shure MV7', price: 24900),
            new Product(name: 'Station d’accueil CalDigit TS4', price: 39900, new: true),
            new Product(name: 'Stand laptop Rain Design mStand', price: 5900),
            new Product(name: 'Clavier Apple Magic Keyboard', price: 12900),
            new Product(name: 'Lampe BenQ ScreenBar Halo', price: 18900, new: true),
        ];
    }

    /**
     * @return list<Product>
     */
    public function search(string $query): array
    {
        $needle = mb_strtolower(trim($query));

        if ('' === $needle) {
            return [];
        }

        return array_values(array_filter(
            $this->all(),
            static fn (Product $p): bool => str_contains(mb_strtolower($p->name), $needle),
        ));
    }
}
