# Live Components Demo

Support de présentation et démos interactives autour de **Symfony UX Live Components**.

Le projet sert deux usages complémentaires :

1. **Une présentation reveal.js** (`/presentation`) découpée en 7 chapitres, qui raconte le passage d'un front JS classique à des composants Twig rendus côté serveur.
2. **Des démos isolées** (`/demo/{slug}`) qui illustrent chaque notion du talk : Twig Components, Live Components, binding, actions, cycle de vie, etc.

## Stack

- **Framework** : Symfony 8.0+ (PHP 8.4+, `declare(strict_types=1)` partout)
- **Serveur local** : Symfony CLI (proxy HTTPS `*.wip`)
- **Base de données** : SQLite (fichier `var/data.db`)
- **Front** : Tailwind CSS 4, Stimulus, AssetMapper
- **Symfony UX** : Live Component, Turbo, Icons
- **Tests** : PHPUnit 12
- **Qualité** : PHPStan (level 9), PHP-CS-Fixer
- **Async** : Symfony Messenger (transport Doctrine)
- **AI / Debug** : serveurs MCP `symfony-ai-mate` et `chrome-devtools`

## Contenu

### Présentation (`/presentation`)

Structure reveal.js dans `templates/presentation/` :

| Chapitre | Sujet                          |
|----------|--------------------------------|
| 01       | Problème et contexte           |
| 02       | Du JS au PHP                   |
| 03       | Twig Components                |
| 04       | Live Components                |
| 05       | Démo live                      |
| 06       | Tests, perf, sécurité          |
| 07       | Synthèse                       |

Les plans détaillés de chaque chapitre sont dans `docs/plan/`.

### Démos (`/demo/{slug}`)

Composants implémentés dans `src/Twig/Components/` + `templates/components/` :

- `counter` — Live Component minimal (state + action)
- `alert` — Twig Component simple (props, slot)
- `product-card` — Twig Component avec service injecté (`PriceFormatter`)
- `product-search` — Live Component avec `LiveProp` writable et recherche réactive
- `source-viewer` — utilitaire d'affichage du code source d'un composant

## Prérequis

- [PHP 8.4+](https://www.php.net/) avec l'extension `pdo_sqlite`
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)
- [Node.js](https://nodejs.org/) (Tailwind + outils MCP)

## Installation

```bash
make init            # composer install + npm install + tailwind:build + asset-map:compile
make db-reset        # recrée var/data.db (migrations + fixtures)
make serve           # démarre le serveur Symfony
```

> 💡 `make` (ou `make help`) liste toutes les cibles disponibles.

Le fichier `.symfony.local.yaml` démarre automatiquement Tailwind en mode watch et le worker Messenger.

## Commandes utiles

Toutes les commandes PHP passent par `symfony` — jamais `php` directement.

```bash
symfony serve                                        # Serveur dev
symfony console make:migration                       # Après modif d'entité
symfony php bin/phpunit                              # Tests Unit + Functional
symfony php vendor/bin/phpstan analyse               # Analyse statique (level 9)
symfony php vendor/bin/php-cs-fixer fix              # Code style
make quality                                         # cs-fix + phpstan + build
```

## Identifiants de test

- `admin@example.com` / `password` (ROLE_USER)

## Organisation du code

```
src/
├── Controller/          # PresentationController, DemoController, SecurityController
├── Demo/                # Product, ProductRepository, PriceFormatter, SourceFileReader
├── Entity/              # Entités Doctrine
├── Repository/
└── Twig/
    ├── Components/      # Twig + Live Components de la démo
    └── FileLinkExtension.php

templates/
├── components/          # Templates des composants (Alert, Counter, ProductCard, …)
├── demo/                # Pages /demo/{slug} qui montent chaque composant
└── presentation/        # reveal.js + chapitres

docs/plan/               # Plan éditorial des 7 chapitres
fixtures/                # DataFixtures\ (PSR-4) — PAS dans src/
```

Voir `CLAUDE.md` pour les règles d'architecture (Controller → Service → Repository → Entity) et les conventions de code.

## Serveurs MCP

`.mcp.json` configure deux serveurs pour l'assistance IA :

| Serveur             | Description                                                    |
|---------------------|----------------------------------------------------------------|
| **symfony-ai-mate** | Accès au profiler Symfony, logs Monolog, services du container |
| **chrome-devtools** | Interaction avec Chrome via DevTools Protocol                  |
