.DEFAULT_GOAL := help
.PHONY: init serve stop db-reset fixtures phpstan cs-fix build quality help

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*##' $(MAKEFILE_LIST) | sort | awk -F ':.*## ' '{printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

## Serveur

init: ## Installe les dépendances et build les assets
	symfony composer install
	npm install
	symfony console tailwind:build

serve: ## Lance le serveur Symfony
	symfony serve

stop: ## Arrête le serveur Symfony
	symfony server:stop

## Base de données

db-reset: ## Recrée la base SQLite from scratch (migrations + fixtures)
	rm -f var/data.db
	symfony console doctrine:migrations:migrate -n
	symfony console doctrine:fixtures:load -n

fixtures: ## Charge les fixtures Doctrine
	symfony console doctrine:fixtures:load -n

## Qualité

phpstan: ## Analyse statique (niveau 9)
	symfony php vendor/bin/phpstan analyse

cs-fix: ## Correction automatique avec PHP CS Fixer
	symfony php vendor/bin/php-cs-fixer fix

build: ## Build des assets (Tailwind + AssetMapper)
	symfony console tailwind:build --minify
	symfony console asset-map:compile

quality: cs-fix phpstan build ## Lance toute la QA (CS Fixer + PHPStan + build)
