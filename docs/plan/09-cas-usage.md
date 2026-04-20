# 🧪 9. Cas d'usage concrets

## 🎯 Objectif du chapitre

Transformer la théorie en **recettes opérationnelles** — du code qu'on peut poser en production demain matin.

À la fin du chapitre, on doit pouvoir répondre à :

1. **Comment choisir** entre Twig Component et Live Component sur une feature donnée ?
2. **À quoi ressemble** un Live Component de recherche, de formulaire conditionnel, de liste filtrable, d'action rapide ?
3. **Quelles sont les erreurs** fréquentes et comment les éviter ?

> ⚠️ **À retenir** : le chapitre 8 nous a donné la grille de critères. Ici, on l'applique sur des cas réels — pas des toys, des patterns qu'on retrouve dans 90 % des backoffices Symfony.

---

## Slide 9.1 — La règle des deux questions

Avant de choisir la brique, deux questions suffisent dans 95 % des cas :

```
1. Y a-t-il une interaction utilisateur qui modifie l'UI ?
   └─ Non → Twig Component (rendu figé, props au chargement)
   └─ Oui  → Question 2

2. Cette interaction dépend-elle d'un état ou de données côté serveur ?
   └─ Non → Stimulus (comportement purement client)
   └─ Oui → Live Component
```

### Ce que ça donne sur des exemples courants

| Feature | Q1 | Q2 | Brique |
|---------|----|----|--------|
| Card produit (affichage) | Non | — | **Twig Component** |
| Badge statut commande | Non | — | **Twig Component** |
| Toggle dark mode | Oui | Non | **Stimulus** |
| Recherche produit en live | Oui | Oui (BDD) | **Live Component** |
| Filtre + pagination liste | Oui | Oui (BDD) | **Live Component** |
| Champs conditionnels form | Oui | Oui (règles PHP) | **Live Component** |
| Bouton "liker" | Oui | Oui (compteur persisté) | **Live Component** |
| Copy-to-clipboard | Oui | Non | **Stimulus** |
| Éditeur WYSIWYG inline | Oui | Non (état client) | **Stimulus + lib JS** |

**Règle d'or** : si la réponse côté serveur est nécessaire pour savoir quoi afficher, Live Component. Sinon, pas besoin.

---

## Slide 9.2 — Quand utiliser Twig Components

### ✅ Cas typiques

- **UI statique réutilisable** : boutons, badges, alerts, tooltips, avatars, breadcrumbs
- **Design system interne** : un seul endroit pour changer le style d'un bouton sur tout le produit
- **Blocks de layout** : header, sidebar, footer, navigation
- **Cards & listings** : cards produit, cards utilisateur (sans interaction live)
- **Composants "data display"** : tables read-only, fiches détails, dashboards

### Exemple : design system interne

```twig
{# Cohérence visuelle garantie partout #}
<twig:Button variant="primary" icon="plus" label="Nouvelle commande" />
<twig:Button variant="danger" icon="trash" label="Supprimer" :disabled="not is_granted('DELETE', order)" />

{# Affichage conditionnel selon les droits — calculé côté PHP, pas de JS #}
<twig:ProductCard :product="product" :canEdit="is_granted('EDIT', product)" />

{# Alerte dans le layout #}
<twig:Alert type="warning">
    Votre session expire dans 5 minutes.
</twig:Alert>
```

### Ce qu'on gagne

- **Contrat de typage** : `variant` est une enum PHP, pas une string libre
- **Pas de round-trip** : le rendu est inclus dans la réponse initiale, zéro requête Ajax
- **Refactoring sécurisé** : PHPStan vérifie les props, l'IDE autocomplète

> ⚠️ **Signal d'alarme** : si tu ajoutes un `data-action` Stimulus pour bricoler de l'Ajax sur un Twig Component, c'est le moment de passer en Live Component.

---

## Slide 9.3 — Quand utiliser Live Components

### ✅ Cas typiques

- **Formulaires dynamiques** : champs conditionnels, wizards multi-étapes, validation live par champ
- **Recherche / autocomplete** : search-as-you-type, suggestions en temps réel
- **Filtres & listings dynamiques** : liste produit filtrable sans rechargement, pagination fluide
- **Mini-actions contextuelles** : compteur, like, bookmark, quick edit inline
- **Panier / cart dynamique** : ajout/retrait article, mise à jour du total en live

### Aperçu des patterns (code développé dans les slides suivantes)

```twig
{# Recherche live produit #}
<twig:ProductSearch />

{# Liste filtrable avec pagination #}
<twig:ProductList :categoryId="category.id" />

{# Formulaire avec champs conditionnels #}
<twig:CompanyForm />

{# Bouton like avec compteur live #}
<twig:LikeButton :postId="post.id" :initialCount="post.likesCount" />

{# Compteur — cas d'école pour la démo #}
<twig:Counter :initialValue="0" />
```

### Le pattern commun à tous ces composants

```
Attribut PHP → LiveProp  : porte l'état, survivant entre les re-renders
Méthode PHP → LiveAction : répond aux actions utilisateur
Template Twig            : projection de l'état, re-rendu à chaque cycle
DOM morphing             : seul le diff est appliqué côté client
```

---

## Slide 9.4 — Exemple détaillé : recherche live produit

### Côté PHP

```php
<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProductSearch
{
    use DefaultActionTrait;

    // writable: true → le champ input peut modifier cette prop directement
    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function getResults(): array
    {
        if (mb_strlen($this->query) < 2) {
            return [];
        }

        return $this->products->searchByName($this->query, limit: 10);
    }
}
```

### Côté Twig

```twig
<div {{ attributes }}>
    <div class="relative">
        <input
            type="search"
            data-model="debounce(300)|query"
            placeholder="Rechercher un produit…"
            class="w-full rounded border px-3 py-2"
        >
        {# data-loading masque/affiche automatiquement pendant le round-trip #}
        <span data-loading class="absolute right-3 top-2 text-gray-400">…</span>
    </div>

    {% if this.results is not empty %}
        <ul class="mt-2 divide-y rounded border bg-white shadow">
            {% for product in this.results %}
                <li class="flex items-center justify-between px-3 py-2">
                    <span>{{ product.name }}</span>
                    <span class="text-gray-500">{{ product.price|number_format(2, ',', ' ') }} €</span>
                </li>
            {% endfor %}
        </ul>
    {% elseif query|length >= 2 %}
        <p class="mt-2 text-sm text-gray-500">Aucun résultat pour "{{ query }}".</p>
    {% endif %}
</div>
```

### Ce qu'on obtient

| Résultat | Mécanisme |
|----------|-----------|
| Zéro endpoint JSON à créer | Le re-render appelle `getResults()` à chaque cycle |
| Debounce natif (300 ms) | `data-model="debounce(300)\|query"` |
| Indicateur de chargement | `data-loading` (attribut natif Live Components) |
| Testable en PHPUnit | `$this->products->searchByName()` est un service ordinaire |
| Aucun JS écrit | Stimulus + live controller, livré par le bundle |

---

## Slide 9.5 — Exemple détaillé : formulaire conditionnel

### Le cas : formulaire d'inscription entreprise / particulier

```php
<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Enum\Type\AccountType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[AsLiveComponent]
final class CompanyForm
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    #[Assert\NotBlank]
    public string $name = '';

    #[LiveProp(writable: true)]
    public AccountType $type = AccountType::Individual;

    // N'apparaît dans le form que si type === Enterprise
    #[LiveProp(writable: true)]
    #[Assert\When(
        expression: 'this.type.value === "enterprise"',
        constraints: [new Assert\NotBlank(), new Assert\Regex('/^\d{14}$/')]
    )]
    public string $siret = '';

    // N'apparaît que si type === Enterprise
    #[LiveProp(writable: true)]
    public string $vatNumber = '';

    public function isEnterprise(): bool
    {
        return $this->type === AccountType::Enterprise;
    }

    #[LiveAction]
    public function save(AccountRepository $accounts): RedirectResponse
    {
        // La validation Symfony est automatiquement rejouée ici
        $account = $accounts->createFromComponent($this);

        return $this->redirectToRoute('account_show', ['id' => $account->getId()]);
    }
}
```

### Côté Twig

```twig
<form {{ attributes }} data-action="submit->live#action" data-live-action-param="save">
    <div class="space-y-4">
        {# Sélecteur de type — change l'UI instantanément #}
        <div>
            <label>Type de compte</label>
            <select data-model="type">
                <option value="individual">Particulier</option>
                <option value="enterprise">Entreprise</option>
            </select>
        </div>

        <div>
            <label>Nom {{ isEnterprise() ? 'de la société' : 'complet' }}</label>
            <input type="text" data-model="on(input)|name">
        </div>

        {# Bloc conditionnel : affiché seulement si type === enterprise #}
        {% if this.isEnterprise() %}
            <div>
                <label>Numéro SIRET</label>
                <input type="text" data-model="on(blur)|siret" placeholder="14 chiffres">
            </div>
            <div>
                <label>Numéro de TVA</label>
                <input type="text" data-model="vatNumber">
            </div>
        {% endif %}

        <button type="submit">Créer le compte</button>
    </div>
</form>
```

### Ce qu'on obtient

- **Champs conditionnels sans JS** : changer le `type` déclenche un re-render, les champs SIRET/TVA apparaissent/disparaissent
- **Validation PHP** : les contraintes `Assert\When` s'appliquent selon le type, rien à dupliquer en JS
- **Un seul round-trip** au changement de `type` : pas de cascade de requêtes
- **`data-model="on(blur)|siret"`** : validation au blur uniquement, pas à chaque frappe

---

## Slide 9.6 — Exemple détaillé : liste filtrable + pagination

### Côté PHP

```php
<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProductList
{
    use DefaultActionTrait;

    // url: true → l'état va dans l'URL (back/forward fonctionnent, shareable link)
    #[LiveProp(writable: true, url: true)]
    public string $search = '';

    #[LiveProp(writable: true, url: true)]
    public string $sortBy = 'name';

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    private const PER_PAGE = 20;

    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function getProducts(): array
    {
        return $this->products->findFiltered(
            search: $this->search,
            sortBy: $this->sortBy,
            page: $this->page,
            perPage: self::PER_PAGE,
        );
    }

    public function getTotalPages(): int
    {
        return (int) ceil(
            $this->products->countFiltered($this->search) / self::PER_PAGE
        );
    }
}
```

### Côté Twig

```twig
<div {{ attributes }}>
    {# Barre de filtres #}
    <div class="flex gap-4 mb-4">
        <input
            type="search"
            data-model="debounce(300)|search"
            placeholder="Rechercher…"
            class="flex-1"
        >
        <select data-model="sortBy">
            <option value="name">Nom A–Z</option>
            <option value="price_asc">Prix croissant</option>
            <option value="price_desc">Prix décroissant</option>
            <option value="updated_at">Récents</option>
        </select>
    </div>

    {# Tableau — re-rendu automatique à chaque changement de filtre #}
    <table class="w-full">
        <tbody>
            {% for product in this.products %}
                <tr>
                    <td>{{ product.name }}</td>
                    <td>{{ product.price|number_format(2, ',', ' ') }} €</td>
                    <td>{{ product.stock }}</td>
                </tr>
            {% else %}
                <tr><td colspan="3" class="text-center text-gray-500">Aucun résultat</td></tr>
            {% endfor %}
        </tbody>
    </table>

    {# Pagination #}
    {% if this.totalPages > 1 %}
        <nav class="flex gap-2 mt-4">
            {% for p in 1..this.totalPages %}
                <button
                    data-action="live#action"
                    data-live-action-param="$set(page={{ p }})"
                    class="{{ p === page ? 'font-bold' : '' }}"
                >{{ p }}</button>
            {% endfor %}
        </nav>
    {% endif %}
</div>
```

### Ce qu'on obtient

- **Filtres + tri + pagination** sans une ligne de JS, sans un endpoint API
- **`url: true`** : l'URL reflète l'état (`?search=velo&sortBy=price_asc&page=2`) — partage de lien, retour arrière fonctionnel
- **`$set(page=N)`** : action inline sans méthode PHP supplémentaire
- **Re-render partiel** : seul le tableau est remplacé via DOM morphing, pas la page entière

---

## Slide 9.7 — Exemple : quick action (like / bookmark)

### Le cas : bouton "liker" un article, avec mise à jour instantanée du compteur

```php
<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Repository\PostRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class LikeButton
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $postId;

    #[LiveProp]
    public int $count;

    #[LiveProp]
    public bool $liked = false;

    public function __construct(
        private readonly PostRepository $posts,
        private readonly Security $security,
    ) {}

    #[LiveAction]
    public function toggle(): void
    {
        $post = $this->posts->find($this->postId);
        $user = $this->security->getUser();

        if ($this->liked) {
            $post->removeLike($user);
            $this->count--;
        } else {
            $post->addLike($user);
            $this->count++;
        }

        $this->liked = !$this->liked;
        $this->posts->save($post);
    }
}
```

### Côté Twig

```twig
<button
    {{ attributes }}
    data-action="live#action"
    data-live-action-param="toggle"
    class="{{ liked ? 'text-red-500' : 'text-gray-400' }} flex items-center gap-1"
>
    <twig:Icon name="{{ liked ? 'heart-solid' : 'heart' }}" />
    <span>{{ count }}</span>
</button>
```

### Usage dans la page

```twig
{% for post in posts %}
    <article>
        <h2>{{ post.title }}</h2>
        <twig:LikeButton
            :postId="post.id"
            :count="post.likesCount"
            :liked="post.isLikedBy(app.user)"
        />
    </article>
{% endfor %}
```

### Ce qu'on obtient

- **UX instantanée** : le compteur et l'icône changent au clic via DOM morphing
- **Persistance automatique** : `toggle()` écrit en base, pas de endpoint custom
- **Réutilisable** : `<twig:LikeButton>` fonctionne sur n'importe quelle entité (post, commentaire, produit…) — il suffit de changer le `postId`
- **Sécurisé** : `$this->security->getUser()` est injecté côté PHP, jamais exposé au client

---

## Slide 9.8 — Exemple d'école : le compteur

### Cas minimal, idéal pour la démo

```php
<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Counter
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $count = 0;

    #[LiveAction]
    public function increment(): void { $this->count++; }

    #[LiveAction]
    public function decrement(): void { $this->count--; }

    #[LiveAction]
    public function reset(): void { $this->count = 0; }
}
```

```twig
<div {{ attributes }} class="flex items-center gap-4">
    <button data-action="live#action" data-live-action-param="decrement">−</button>
    <span class="text-2xl font-bold tabular-nums">{{ count }}</span>
    <button data-action="live#action" data-live-action-param="increment">+</button>
    <button data-action="live#action" data-live-action-param="reset" class="text-sm text-gray-400">reset</button>
</div>
```

### Pourquoi c'est l'exemple de démo parfait

- **~30 lignes** au total, lisibles en 30 secondes depuis une slide
- Montre les **trois concepts fondamentaux** en une fois : `LiveProp` (état), `LiveAction` (mutation), re-render automatique
- **Zéro JS écrit à la main** : tout passe par les attributs `data-action`
- Réaction **visuelle immédiate** : l'audience voit le chiffre changer sans rechargement de page

---

## Slide 9.9 — Anti-patterns à éviter

> Cette slide pose les cas à reconnaître rapidement. Le chapitre 10 les développe en profondeur.

### ❌ Live Component pour du markup purement statique

Si le composant n'a **ni `LiveProp` writable ni `LiveAction`**, c'est un **Twig Component**. Le coût de l'hydratation est payé pour rien.

### ❌ Entité Doctrine complète dans une `LiveProp`

```php
// ❌ Mauvaise idée : sérialise et expose toute l'entité dans le DOM
#[LiveProp(writable: true)]
public Order $order;

// ✅ Passer l'ID, recharger côté serveur
#[LiveProp]
public int $orderId;

public function getOrder(): Order
{
    return $this->orders->find($this->orderId);
}
```

**Problèmes** : payload réseau gonflé, risque d'exposer des champs sensibles, hydratation fragile.

### ❌ Cascade de `LiveAction` pour une seule action utilisateur

Regrouper la logique dans **une seule méthode** — chaque `LiveAction` = un round-trip HTTP. Trois actions enchaînées = trois allers-retours, latence perçue × 3.

### ❌ Forcer Live là où la latence tue l'UX

| Ce cas | Utiliser à la place |
|--------|---------------------|
| Slider couleur (preview live) | Stimulus pur (client-side) |
| Éditeur WYSIWYG | TipTap / ProseMirror |
| Canvas de dessin | JS natif ou librairie cliente |
| Carte Leaflet interactive | Stimulus + `ux-leaflet-map` |

---

## 💬 Message clé

> **"Le bon composant au bon endroit."**
>
> Twig Component pour la structure et le design system. Live Component pour tout ce qui lit ou écrit de l'état serveur. Stimulus pour les comportements purement clients. Et SPA **uniquement** là où la latence réseau devient un vrai problème — pas un problème fantasmé.

---

## 🗣️ Narration (script oral)

> "Ces exemples ne sont pas des cas d'école : ce sont littéralement **90 % de ce qu'on fait** dans un backoffice Symfony. Recherche full-text, formulaires avec champs conditionnels, listes filtrables avec pagination, boutons d'action rapide — tout ça, avant, demandait un mélange de Stimulus custom, d'endpoints JSON, de fetch(), et de code JS dupliqué côté client pour la validation. C'était du vrai travail.
>
> Avec Live Components, c'est une **classe PHP, un template Twig, et deux ou trois attributs `data-`**. Le code PHP fait exactement ce qu'il fait dans un controller classique — il lit la BDD, applique les règles métier, retourne des données. La seule différence, c'est que le résultat est injecté dans le DOM sans rechargement. Il n'y a pas de nouvelle magie à apprendre.
>
> La règle des deux questions — 'y a-t-il une interaction ? cette interaction dépend-elle du serveur ?' — elle tient dans une phrase. Et dans 95 % des cas, elle suffit à trancher en trente secondes."

---

## 🧭 Transition vers le chapitre 10

On a vu le potentiel. Soyons maintenant honnêtes : **avantages concrets, limites réelles, et anti-patterns à connaître avant de livrer en prod**.
