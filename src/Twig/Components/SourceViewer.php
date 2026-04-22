<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Demo\SourceFileReader;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class SourceViewer
{
    /** @var list<string> */
    public array $files = [];

    public function __construct(
        private readonly SourceFileReader $reader,
    ) {
    }

    /**
     * @return list<array{path: string, language: string, content: string}>
     */
    public function sources(): array
    {
        return array_map(fn (string $path): array => $this->reader->read($path), $this->files);
    }
}
