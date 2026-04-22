<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

// Component interactif : l'état vit côté serveur, re-rendu à chaque action
#[AsLiveComponent]
final class Counter
{
    // Fournit l'action `_default` requise par le cycle de re-rendu
    use DefaultActionTrait;

    // État synchronisé client/serveur. `writable: true` autorise la modification depuis le front
    #[LiveProp(writable: true)]
    public int $count = 0;

    // Méthode appelable depuis le front via data-action="live#action"
    #[LiveAction]
    public function increment(): void
    {
        ++$this->count;
    }

    #[LiveAction]
    public function decrement(): void
    {
        --$this->count;
    }

    #[LiveAction]
    public function reset(): void
    {
        $this->count = 0;
    }
}
