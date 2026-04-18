<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final class DemoController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    #[Route(
        path: '/demo/{slug}',
        name: 'app_demo',
        requirements: ['slug' => '[a-z0-9-]+'],
    )]
    public function show(string $slug): Response
    {
        $template = \sprintf('demo/%s.html.twig', $slug);

        if (!$this->twig->getLoader()->exists($template)) {
            throw $this->createNotFoundException(\sprintf('Demo "%s" introuvable.', $slug));
        }

        return $this->render($template, [
            'slug' => $slug,
        ]);
    }
}
