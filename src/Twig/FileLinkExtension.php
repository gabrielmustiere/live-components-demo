<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\ErrorHandler\ErrorRenderer\FileLinkFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FileLinkExtension extends AbstractExtension
{
    public function __construct(
        private readonly FileLinkFormatter $fileLinkFormatter,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_link', $this->fileLink(...)),
        ];
    }

    public function fileLink(string $path, int $line = 1): string
    {
        $absolute = str_starts_with($path, '/') ? $path : $this->projectDir.'/'.$path;
        $link = $this->fileLinkFormatter->format($absolute, $line);

        return false === $link ? '' : $link;
    }
}
