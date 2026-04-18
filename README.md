# Symfony Template

Ce projet est un squelette (template) pour les nouvelles applications Symfony, pré-configuré avec les outils modernes de
développement.

## Fonctionnalités

- **Framework** : Symfony 8.0+
- **Serveur local** : Intégration complète avec le CLI Symfony (proxy HTTPS `*.wip`)
- **Base de données** : SQLite (fichier local `var/data.db`)
- **Assets** : Tailwind CSS 4 via Symfony UX
- **Auth** : Authentification par formulaire (email/password)
- **Tests** : PHPUnit 12 (Unit + Functional)
- **Qualité** : PHPStan (level 9) + PHP-CS-Fixer
- **Async** : Symfony Messenger (transport Doctrine)
- **AI** : Serveurs MCP intégrés (Symfony AI Mate, Chrome DevTools)

## Prérequis

- [PHP 8.4+](https://www.php.net/) avec l'extension `pdo_sqlite`
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)
- [Node.js](https://nodejs.org/) (pour Tailwind et les outils MCP)

## Installation

1. **Cloner le projet**
2. **Installer les dépendances et démarrer le serveur**
   ```bash
   make init serve
   ```
   La cible `init` installe Composer, npm et build Tailwind ; `serve` démarre le serveur Symfony.
3. **Créer la base de données** (première exécution ou reset complet)
   ```bash
   make db-reset
   ```
4. **Variables d'environnement** (optionnel)

   Copiez le fichier `.env` en `.env.local` pour surcharger des variables.

> 💡 Exécutez `make` (ou `make help`) pour lister toutes les cibles disponibles.

## Workflow de développement

### Commandes utiles

- **Lancer les workers (Tailwind, Messenger)** :
  Le fichier `.symfony.local.yaml` configure le démarrage automatique de Tailwind en mode watch et du worker Messenger
  via le CLI Symfony.
- **Accéder à la base de données** :
  Le fichier SQLite se trouve dans `var/data.db`. N'importe quel client SQLite peut l'ouvrir.
- **E-mails** :
  Le mailer est configuré sur `null://null` en dev/test (les mails sont avalés). Pour activer un envoi réel,
  surchargez `MAILER_DSN` dans `.env.local`.

### Tests

```bash
symfony php bin/phpunit                              # Tests Unit + Functional
```

### Qualité de code

```bash
symfony php vendor/bin/phpstan analyse               # Analyse statique (level 9)
symfony php vendor/bin/php-cs-fixer fix              # Code style
```

## Serveurs MCP (Claude Code)

Le fichier `.mcp.json` configure deux serveurs MCP pour l'assistance IA :

| Serveur             | Description                                                    |
|---------------------|----------------------------------------------------------------|
| **symfony-ai-mate** | Accès au profiler Symfony, logs Monolog, services du container |
| **chrome-devtools** | Interaction avec Chrome via DevTools Protocol                  |

## État d'avancement (v1.0.0)

- [x] Configuration Symfony CLI
- [x] Base de données SQLite (zéro dépendance externe)
- [x] Installation Tailwind CSS 4
- [x] Configuration PHP-CS-Fixer / PHPStan
- [x] Configuration Editorconfig
- [x] Création du template de base (base.html.twig)
- [x] Installation Symfony UX (Icons, Flash Messages, Live Component)
- [x] Authentification par formulaire (login/logout)
- [x] Serveurs MCP (Symfony AI Mate, Chrome DevTools)
