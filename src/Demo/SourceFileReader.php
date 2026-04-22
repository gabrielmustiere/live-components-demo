<?php

declare(strict_types=1);

namespace App\Demo;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class SourceFileReader
{
    /** @var list<string> */
    private const array ALLOWED_PREFIXES = [
        'src/Twig/Components/',
        'src/Demo/',
        'src/Controller/',
        'templates/components/',
        'templates/demo/',
    ];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array{path: string, language: string, content: string}
     */
    public function read(string $relativePath): array
    {
        $this->assertAllowed($relativePath);

        $absolute = $this->projectDir . '/' . $relativePath;
        $real = realpath($absolute);

        if (false === $real || !str_starts_with($real, $this->projectDir . \DIRECTORY_SEPARATOR)) {
            throw new \InvalidArgumentException(\sprintf('Chemin interdit : %s', $relativePath));
        }

        $content = file_get_contents($real);

        if (false === $content) {
            throw new \RuntimeException(\sprintf('Impossible de lire %s', $relativePath));
        }

        return [
            'path' => $relativePath,
            'language' => $this->languageFor($relativePath),
            'content' => $content,
        ];
    }

    private function assertAllowed(string $relativePath): void
    {
        if (str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
            throw new \InvalidArgumentException(\sprintf('Chemin invalide : %s', $relativePath));
        }

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($relativePath, $prefix)) {
                return;
            }
        }

        throw new \InvalidArgumentException(\sprintf('Chemin non autorisé : %s', $relativePath));
    }

    private function languageFor(string $path): string
    {
        return match (true) {
            str_ends_with($path, '.twig') => 'twig',
            str_ends_with($path, '.php') => 'php',
            default => 'plaintext',
        };
    }
}
