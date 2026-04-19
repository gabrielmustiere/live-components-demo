# Squelette minimal — Démo Live Component

Pattern à suivre pour **créer une démo interactive** attachée à un chapitre. Calé sur l'exemple `Counter` du projet, qui sert de référence.

---

## 1. Le composant PHP — `src/Twig/Components/Xxx.php`

```php
<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Xxx
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $count = 0;

    #[LiveAction]
    public function increment(): void
    {
        ++$this->count;
    }
}
```

**Règles projet** :
- `declare(strict_types=1)` obligatoire.
- `final class`.
- `use DefaultActionTrait;` pour bénéficier de l'action par défaut.
- `#[LiveProp(writable: true)]` uniquement si le front doit pouvoir écrire (ex. input bindé). Sinon, omettre `writable`.
- `#[LiveAction]` pour chaque méthode invoquée par le front via `data-live-action-param`.

---

## 2. Le template du composant — `templates/components/Xxx.html.twig`

```twig
<div {{ attributes.defaults({ class: 'flex flex-col items-center gap-6' }) }}>
    <div class="text-6xl font-bold tabular-nums text-gray-900">{{ count }}</div>

    <div class="flex items-center gap-3">
        <button
            type="button"
            data-action="live#action"
            data-live-action-param="increment"
            class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500"
        >
            +
        </button>
    </div>
</div>
```

**Règles** :
- Racine wrappée par `{{ attributes.defaults({ class: '...' }) }}` → le parent peut surcharger les classes.
- Boutons : `data-action="live#action"` + `data-live-action-param="nomDeLAction"`.
- Classes Tailwind (v4, déjà installé) — cohérent avec le reste du projet.
- Les `LiveProp` publiques du composant sont directement accessibles dans le template (`{{ count }}`).

---

## 3. La page démo standalone — `templates/demo/xxx.html.twig`

```twig
{% extends 'demo/layout.html.twig' %}

{% block demo_page_title %}Titre de l'onglet{% endblock %}
{% block demo_title %}Nom court en header{% endblock %}

{% block demo_body %}
    <h1 class="text-3xl font-bold text-gray-900">Titre H1</h1>
    <p class="mt-2 text-gray-600">
        Sous-titre descriptif de la démo.
    </p>

    <div class="mt-10 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <twig:Xxx />
    </div>

    <section class="mt-10">
        <h2 class="text-xl font-semibold text-gray-900">Code source</h2>
        <p class="mt-2 text-sm text-gray-600">
            Voir
            <a href="{{ file_link('src/Twig/Components/Xxx.php') }}" class="font-mono text-indigo-600 hover:underline"><code>src/Twig/Components/Xxx.php</code></a>
            et
            <a href="{{ file_link('templates/components/Xxx.html.twig') }}" class="font-mono text-indigo-600 hover:underline"><code>templates/components/Xxx.html.twig</code></a>.
        </p>
    </section>
{% endblock %}
```

**Règles** :
- `{% extends 'demo/layout.html.twig' %}` pour la navigation transverse démos.
- `<twig:Xxx />` — syntaxe composant, pas `{{ component('Xxx') }}`.
- Le helper `file_link('chemin/vers/fichier.php')` génère un lien PhpStorm/VSCode vers le fichier (cf. `src/Twig/FileLinkExtension.php`).

---

## 4. La route — `src/Controller/DemoController.php`

Ajouter une méthode publique, typée, avec attribut `#[Route]` :

```php
#[Route(
    path: '/demo/xxx',
    name: 'app_demo_xxx',
)]
public function xxx(): Response
{
    return $this->render('demo/xxx.html.twig');
}
```

---

## 5. Intégration côté slides

**Option A — lien vers la démo standalone** (recommandé quand la démo a besoin d'espace) :

```twig
<section data-slide-id="NN-demo">
    {{ ui.slide_header('N.X', 'Voir en action', 'Démo interactive') }}

    <p>Un compteur Live Component minimal — 1 prop, 3 actions, zéro JS écrit à la main.</p>

    <p>
        <a class="btn-primary" href="{{ path('app_demo_xxx') }}" target="_blank">
            Ouvrir la démo ↗
        </a>
    </p>
</section>
```

**Option B — démo in-place dans la slide** (pour un composant petit et auto-contenu) :

```twig
<section data-slide-id="NN-demo-inline">
    {{ ui.slide_header('N.X', 'Démo in-place') }}

    <div class="rounded-xl bg-white p-6" style="color: #111;">
        <twig:Xxx />
    </div>
</section>
```

⚠️ Les styles du composant (Tailwind classes blanches, gray-900…) sont pensés pour un fond clair. En reveal (fond sombre), encadrer dans un `<div>` à fond clair comme ci-dessus.

---

## 6. Vérifier la démo

```bash
symfony serve
# puis ouvrir http://127.0.0.1:8000/demo/xxx
# et http://127.0.0.1:8000/presentation pour vérifier l'intégration slide
```

Si un `LiveProp` / `LiveAction` ne se comporte pas comme attendu, consulter la doc officielle (les signatures évoluent) :
- https://symfony.com/bundles/ux-live-component/current/index.html

---

## Checklist de création d'une démo

- [ ] Composant PHP créé avec `#[AsLiveComponent]` + `DefaultActionTrait`.
- [ ] `LiveProp` publics, `LiveAction` sur les méthodes front-invoquables.
- [ ] Template `templates/components/Xxx.html.twig` avec `attributes.defaults(...)`.
- [ ] Page démo `templates/demo/xxx.html.twig` extends `demo/layout.html.twig`.
- [ ] Route `#[Route('/demo/xxx', name: 'app_demo_xxx')]` dans `DemoController`.
- [ ] Slide de présentation avec lien `btn-primary` OU intégration in-place (fond clair).
- [ ] Test manuel : `symfony serve` → URL démo → cliquer sur les actions.
- [ ] PHPStan propre (`symfony php vendor/bin/phpstan analyse`).
- [ ] CS-Fixer passé (`symfony php vendor/bin/php-cs-fixer fix`).
