<?php

declare(strict_types=1);

namespace App\Demo;

final readonly class Product
{
    public function __construct(
        public string $name,
        public int $price,
        public bool $new = false,
    ) {
    }
}
