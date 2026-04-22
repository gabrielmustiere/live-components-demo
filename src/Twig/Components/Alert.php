<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

// Rendu serveur simple, sans état ni interactivité
#[AsTwigComponent]
final class Alert
{
    // Les propriétés publiques = paramètres du composant : <twig:Alert message="..." type="warning" />
    public string $message;

    public string $type = 'info';

    public bool $dismissible = false;
}
