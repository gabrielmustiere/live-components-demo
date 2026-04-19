# ⚡ 6. Live Components — Le game changer

## 🎯 Objectif du chapitre

Comprendre **ce qui rend un composant "Live"**, et pourquoi c'est un vrai changement de jeu côté Symfony.

Après le chapitre 5 sur les Twig Components — la brique **statique** — ce chapitre attaque la couche qui change tout : la **réactivité server-driven**. On va répondre à cinq questions :

1. **Pourquoi** un Twig Component ne suffit pas dès qu'on veut de l'interactivité ?
2. **Qu'est-ce qu'un Live Component**, concrètement, dans le code ?
3. **Quelles sont les briques** qu'on va manipuler (`LiveProp`, `LiveAction`, data binding Twig) ?
4. **Comment l'état circule** entre le client et le serveur, sans qu'on écrive de JS ?
5. **Quels sont les pièges** à connaître avant d'en mettre en production ?

> ⚠️ **À retenir** : Live Components est une **surcouche** de Twig Components, pas un remplacement. Tout ce qu'on a vu au chapitre 5 reste vrai — on ajoute juste une couche de **réactivité** quand on en a besoin. C'est de l'**opt-in**, composant par composant.

---

## Slide 6.1 — Le problème restant

### Rappel des chapitres précédents

À ce stade du talk, on a posé deux choses :

- **Chapitre 2** — les **trois douleurs** historiques côté Symfony : pas de typage, pas d'unité d'organisation, pas de réactivité server-driven.
- **Chapitre 5** — les **Twig Components** ont réglé les deux premières : une classe PHP, un template Twig, des props typées, un dossier par composant.

Reste la troisième douleur : **la réactivité**.

### Un Twig Component est statique

Un `<twig:ProductCard ... />` est rendu **une fois**, côté serveur, et c'est terminé :

- Aucune interaction utilisateur ne le met à jour
- Pour qu'il "vive", il faut sortir de Twig et écrire du **Stimulus / Ajax / JS custom**
- L'état utilisateur (un filtre, un compteur, une saisie en cours) **n'existe pas** dans le composant — il vit ailleurs, en JS, ou pas du tout

### Conséquence concrète

Dès qu'on veut un composant réactif — un filtre de produits, un panier qui se met à jour, un formulaire qui valide à la frappe — on retombe dans le scénario du chapitre 2 :

- **Logique métier dupliquée** : règles de filtrage en PHP côté serveur **et** réimplémentées en JS côté client
- **Endpoint custom** par interaction (route + contrôleur + format de réponse improvisé)
- **Friction DX** : un composant "vivant" = 4 fichiers hétérogènes (PHP + Twig + JS Stimulus + contrôleur + parfois route DTO)
- **Synchronisation d'état** fragile entre client et serveur

C'est exactement ce mur que Live Components vient abattre.

---

## Slide 6.2 — Live Components = Twig + réactivité

### Définition

> **Un Twig Component qui se met à jour automatiquement via Ajax.**

L'utilisateur interagit, le composant **ré-exécute son rendu côté serveur**, et le DOM est patché côté client — **sans qu'on écrive une ligne de JS**.

### Ce qui change concrètement

| Sans Live | Avec Live |
|-----------|-----------|
| Écrire un endpoint Ajax dédié | **Aucun endpoint** à écrire |
| Définir un format de réponse (JSON ? HTML ?) | **Du HTML**, c'est tout |
| Réimplémenter la logique en JS | **La logique reste en PHP**, le JS est généré par le bundle |
| Mettre à jour le DOM à la main | **DOM morphing** automatique (morphdom) |
| Gérer le CSRF, la sécurité, la sérialisation à la main | **Géré par le bundle** |

### Le vocabulaire qu'on va utiliser

- **`LiveProp`** : une propriété de la classe PHP **synchronisée** entre client et serveur
- **`LiveAction`** : une méthode PHP **déclenchable** depuis le DOM
- **Data model** : un input Twig **lié** à une `LiveProp` (équivalent du `v-model` de Vue)
- **Re-render** : la régénération du markup du composant après une interaction
- **DOM morphing** : la **fusion ciblée** du nouveau markup avec le DOM existant, sans tout détruire

---

## Slide 6.3 — Comment ça marche (vue d'ensemble)

> 🧭 **Pour situer** : ce schéma est volontairement haut niveau. Le **détail technique** de chaque étape (hydration, action, re-render, patch) fait l'objet du chapitre 7.

```
   ┌─────────────┐
   │   User      │  clic / input / submit
   └──────┬──────┘
          ▼
   ┌─────────────┐
   │  Ajax call  │  payload = état du composant + action
   └──────┬──────┘
          ▼
   ┌─────────────┐
   │     PHP     │  hydrate → exécute action → re-render
   └──────┬──────┘
          ▼
   ┌─────────────┐
   │  HTML diff  │  réponse = nouveau markup
   └──────┬──────┘
          ▼
   ┌─────────────┐
   │  DOM patch  │  morphdom côté client
   └─────────────┘
```

### Ce qui est remarquable

- **Pas de SPA** : aucun routeur côté client, pas de `<App />` racine
- **Pas de React / Vue** : aucun runtime virtuel, aucun JSX
- **Pas d'API JSON custom** à concevoir, documenter, versionner
- Le rendu **reste du Twig** — mêmes filters, mêmes extensions, même héritage

### Ce que livre le bundle

- Un **endpoint interne** (route fournie par `ux-live-component`) — tu n'écris pas de contrôleur
- Un **Stimulus controller** (`live_controller.js`) chargé via le Stimulus bridge — tu ne touches pas à `npm`
- Une **machinerie d'hydration / déshydration** des `LiveProp` (sérialisation JSON dans le DOM)
- Un **algorithme de morphing** qui préserve focus, scroll et inputs non modifiés

---

## Slide 6.4 — Inspirations : la famille "HTML-over-the-wire"

Live Components n'est pas un OVNI. Il s'inscrit dans une **famille d'approches** qui a émergé entre 2018 et 2021 :

| Solution | Stack | Année | Idée centrale |
|----------|-------|------|---------------|
| **Phoenix LiveView** | Elixir | 2018 | État serveur, diffs envoyés par WebSocket |
| **Hotwire / Turbo** | Rails (agnostique) | 2020 | HTML-over-the-wire via Turbo Frames / Streams |
| **Laravel Livewire** | PHP / Laravel | 2020 | Composant PHP réactif, état serveur, requête HTTP |
| **htmx** | Agnostique | 2020 | Attributs HTML déclencheurs de fragments |
| **Symfony UX Live Components** | PHP / Symfony | 2022 | Modèle Livewire-like, idiomes Symfony |

### Philosophie commune

> **"Server-driven UI"** : l'état de vérité vit côté serveur, le client n'est qu'une projection, et on échange du **HTML** plutôt que du JSON.

### Ce que cette famille refuse

- ❌ La **double codebase** (logique métier en PHP **et** en JS)
- ❌ L'**hydration coûteuse** d'un SPA au chargement de la page
- ❌ La **complexité opérationnelle** d'un écosystème JS distinct (build, types, tests, déploiement)
- ❌ Le **SEO compliqué** des SPAs (SSR à reconstruire, meta tags dynamiques)

### Ce qu'elle assume

- ✅ Un **round-trip HTTP** par interaction réactive (acceptable pour 90 % des UIs métier)
- ✅ Une **dépendance au réseau** (l'app est moins offline-friendly qu'un SPA pur)
- ✅ Un **modèle moins adapté** aux UIs ultra-fluides (canvas, drag & drop intensif, animations 60 fps)

---

## Slide 6.5 — Anatomie d'un Live Component

Un Live Component, c'est exactement la même structure qu'un Twig Component — **plus** un attribut et un trait.

### Les trois fichiers

```
src/Twig/Components/ProductSearch.php       ← classe PHP
templates/components/ProductSearch.html.twig ← template
(éventuellement)
assets/controllers/product_search_controller.js ← Stimulus extra (rare)
```

### Le squelette minimal

```php
<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProductSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';
}
```

### Ce qu'il faut savoir lire

- **`#[AsLiveComponent]`** remplace `#[AsTwigComponent]` — c'est le seul changement structurel
- **`use DefaultActionTrait`** ajoute l'action par défaut (`__invoke`) qui permet le re-render sans action explicite
- **`#[LiveProp]`** marque une propriété comme **synchronisée** avec le client
- **`writable: true`** autorise le client à modifier la valeur (sinon elle est en lecture seule côté client)

### Le template associé

```twig
{# templates/components/ProductSearch.html.twig #}
<div {{ attributes }}>
    <input
        type="search"
        data-model="query"
        placeholder="Rechercher un produit…"
    >

    <ul>
        {% for product in this.results %}
            <li>{{ product.name }}</li>
        {% endfor %}
    </ul>
</div>
```

### Trois détails cruciaux

1. **`{{ attributes }}`** sur la racine — c'est ici que le bundle injecte les `data-controller="live"`, l'état sérialisé, le token CSRF
2. **`data-model="query"`** — lie l'input à la `LiveProp` `query` (équivalent du `v-model` de Vue)
3. **`this.results`** — méthode publique de la classe PHP, recalculée à chaque re-render (parfait pour les valeurs dérivées)

---

## Slide 6.6 — `LiveProp` en profondeur

`LiveProp` est **l'attribut central** : tout passe par lui.

### Les modes

```php
#[LiveProp]                        // Lecture seule côté client
public string $locale = 'fr';

#[LiveProp(writable: true)]        // Modifiable depuis le DOM
public string $query = '';

#[LiveProp(writable: ['name', 'email'])]  // Champs spécifiques d'un objet
public ContactDto $contact;
```

### Synchronisation : ce qui se passe à chaque cycle

1. La `LiveProp` est **sérialisée** dans le DOM au render initial (`data-live-props-value="..."`)
2. À chaque interaction, le client la **renvoie** au serveur dans le payload Ajax
3. Le serveur **reconstruit** l'objet à partir de ce payload
4. La méthode action est exécutée, la prop peut changer
5. La nouvelle valeur est **re-sérialisée** dans le DOM lors du re-render

### Hydration / déshydration custom

Pour les objets non-scalaires (entités Doctrine, value objects, DTOs), le bundle utilise par défaut le **Symfony Serializer**. On peut forcer un comportement custom :

```php
#[LiveProp(
    writable: true,
    hydrateWith: 'hydrateProduct',
    dehydrateWith: 'dehydrateProduct',
)]
public Product $product;

public function dehydrateProduct(Product $product): int
{
    return $product->getId();
}

public function hydrateProduct(int $id, ProductRepository $repo): Product
{
    return $repo->find($id) ?? throw new \RuntimeException('Product not found');
}
```

> ⚠️ **Pourquoi c'est important** : sans déshydration custom, une entité Doctrine entière serait sérialisée en JSON dans le DOM. **Stocker un ID** est plus léger, plus sûr (le client ne voit que ce qu'on veut) et évite les problèmes de cycles.

### Synchronisation avec l'URL

```php
#[LiveProp(writable: true, url: true)]
public string $query = '';
```

La prop est **reflétée dans la query string** (`?query=foo`) — utile pour le partage de lien et le back/forward navigateur.

### Validation

Les `LiveProp` sont des **propriétés PHP normales** : on y colle les contraintes Symfony Validator standard.

```php
use Symfony\Component\Validator\Constraints as Assert;

#[LiveProp(writable: true)]
#[Assert\NotBlank]
#[Assert\Email]
public string $email = '';
```

Couplé avec le trait `ValidatableComponentTrait`, on déclenche la validation dans une `LiveAction` et on récupère les erreurs dans le template.

---

## Slide 6.7 — `LiveAction` en profondeur

`LiveAction` marque les **méthodes appelables depuis le DOM**.

### La forme la plus simple

```php
#[AsLiveComponent]
final class Counter
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $count = 0;

    #[LiveAction]
    public function increment(): void
    {
        $this->count++;
    }

    #[LiveAction]
    public function reset(): void
    {
        $this->count = 0;
    }
}
```

```twig
<div {{ attributes }}>
    <p>Compteur : {{ count }}</p>

    <button data-action="live#action" data-live-action-param="increment">
        +1
    </button>
    <button data-action="live#action" data-live-action-param="reset">
        Reset
    </button>
</div>
```

### Action avec arguments

Les arguments sont passés via des `data-live-arg-*` :

```twig
<button
    data-action="live#action"
    data-live-action-param="addToCart"
    data-live-product-id-param="{{ product.id }}"
    data-live-quantity-param="1"
>
    Ajouter au panier
</button>
```

```php
#[LiveAction]
public function addToCart(
    #[LiveArg] int $productId,
    #[LiveArg] int $quantity,
    CartManager $cart,
): void {
    $cart->add($productId, $quantity);
}
```

### Injection de services dans une action

L'autowiring fonctionne **directement dans la signature** de la méthode action — pas besoin d'injecter dans le constructeur si le service n'est utilisé que par une action.

### Retour HTTP custom

Une action peut retourner une `RedirectResponse` ou n'importe quelle `Response` :

```php
#[LiveAction]
public function checkout(): RedirectResponse
{
    return $this->redirectToRoute('app_order_summary');
}
```

Le client gère le redirect comme une navigation Turbo classique.

---

## Slide 6.8 — Data binding côté Twig

Le `data-model` est la deuxième pierre angulaire — il **lie** un input à une `LiveProp` et déclenche un re-render à chaque modification.

### Les variantes

```twig
{# Re-render à chaque frappe (par défaut) #}
<input data-model="query" type="search">

{# Debounce 300 ms — utile pour la recherche #}
<input data-model="debounce(300)|query" type="search">

{# Re-render seulement au blur — utile pour un formulaire #}
<input data-model="on(change)|email" type="email">

{# Pas de re-render automatique — la prop change côté client mais on attend une action #}
<input data-model="norender|message" type="text">
```

### Pour un select / checkbox / radio

```twig
<select data-model="sortBy">
    <option value="name">Nom</option>
    <option value="price">Prix</option>
</select>

<input type="checkbox" data-model="onlyInStock"> En stock seulement
```

### Pour les sous-propriétés d'un objet

```twig
<input data-model="contact.name" type="text">
<input data-model="contact.email" type="email">
```

> ⚠️ Pour que ça fonctionne, la `LiveProp` doit être déclarée `writable: ['name', 'email']` — sinon le client n'a pas le droit de modifier ces champs.

---

## Slide 6.9 — Re-render : qui le déclenche ?

Trois événements provoquent un re-render du composant :

1. **Une `LiveProp` modifiée côté client** (via `data-model`)
2. **Une `LiveAction` exécutée**
3. **Un appel explicite** à `$this->emit(...)` ou `$this->dispatchBrowserEvent(...)`

À chaque re-render, le bundle :

- Re-sérialise les `LiveProp` dans le DOM (le client repart avec un état frais)
- Renvoie le markup complet du composant
- Le client applique un **diff DOM** (morphdom) — seuls les nœuds réellement modifiés sont remplacés

### Ce qui est préservé pendant le morphing

- Le **focus** (l'input en cours de saisie reste actif)
- La **position de scroll**
- Les **inputs non modifiés** (un champ que l'utilisateur tape pendant le re-render n'est pas écrasé)
- Les **classes CSS et événements** des éléments qui n'ont pas changé

---

## Slide 6.10 — Aperçu des features avancées

On ne va pas tout couvrir ici (cf. chapitres 9 et démo), mais voici **ce que le bundle propose** au-delà du basique :

### Loading states

```twig
<button data-action="live#action" data-live-action-param="save">
    <span data-loading="hide">Enregistrer</span>
    <span data-loading="show">Enregistrement…</span>
</button>
```

Le bundle ajoute / retire des classes CSS pendant la requête Ajax. On peut aussi **désactiver** un bouton (`data-loading="addAttribute(disabled)"`).

### Polling

```twig
<div {{ attributes }} data-poll="delay(5000)|$render">
    Notifications : {{ count }}
</div>
```

Le composant se re-render automatiquement toutes les 5 secondes — utile pour des compteurs, des statuts de jobs.

### Embedded components

Un Live Component peut **contenir** un autre Live Component, chacun avec son propre état. La communication se fait via **emit** / **listen** :

```php
// Dans le composant enfant
$this->emit('product:added', ['id' => $productId]);

// Dans le composant parent
#[LiveListener('product:added')]
public function onProductAdded(#[LiveArg] int $id): void
{
    // ...
}
```

### Hooks de cycle de vie

```php
#[PreReRender]
public function beforeRender(): void
{
    $this->lastRenderedAt = new \DateTimeImmutable();
}
```

Détaillé au chapitre 7.

---

## Slide 6.11 — Les pièges classiques

À connaître **avant** de pousser en production :

### 1. Tout `LiveProp` `writable` est une entrée utilisateur

Le client peut envoyer **n'importe quelle valeur** dans le payload. **Valider** systématiquement (Symfony Validator, type strict, contraintes métier).

### 2. Stocker des entités complètes dans une `LiveProp` est dangereux

- **Sérialisation lourde** dans le DOM (tout l'objet en JSON)
- **Fuite de données** (champs internes sérialisés)
- **Cycles** Doctrine qui plantent le serializer

→ Toujours préférer un **ID** + déshydration custom (cf. slide 6.6).

### 3. Une action = un round-trip HTTP

Un input avec `data-model` sans debounce sur un champ texte = une requête **par frappe**. Sur 4G, c'est lent. Toujours :

- Utiliser `debounce(300)` pour la recherche live
- Utiliser `on(change)` pour les selects
- Utiliser `norender` quand on veut juste maintenir l'état sans recalculer

### 4. Le composant est **stateless** entre deux requêtes

Aucun état hors `LiveProp` ne survit. Une variable d'instance non `LiveProp` ? Perdue au prochain cycle. Une session, un cache ? Possibles, mais explicites.

### 5. Le morphing peut surprendre sur les listes

Si on rend une liste dont l'ordre change, morphdom peut **réutiliser** un nœud DOM existant pour un autre item — ce qui crée des bugs visuels. Solution : ajouter des **clés stables** (`id="item-{{ item.id }}"`) sur les éléments de liste.

---

## Slide 6.12 — Récap : la grille du chapitre 2 cochée

Reprenons les cinq critères du chapitre 2 et regardons où Live Components nous mène :

| Critère | Live Components |
|---------|:---------------:|
| **Encapsulation logique** | ✅ Classe PHP avec services injectables |
| **Template séparé** | ✅ Twig dédié, syntaxe `<twig:...>` |
| **Props typées** | ✅ Propriétés PHP typées + `LiveProp` |
| **État interne** | ✅ Toute `LiveProp` survit entre les interactions |
| **Performance** | ⚠️ 1 round-trip Ajax par interaction réactive |
| **Interactivité** | ✅ Server-driven, sans JS custom |

C'est **la première ligne** de tout le tableau du chapitre 2 où **toutes les cases** (sauf une nuance perf assumée) sont vertes.

---

## 💬 Message clé

> **"Live Components rendent l'interactivité disponible sans quitter PHP/Twig."**
> On obtient 80 % des bénéfices d'un SPA avec 20 % de la complexité.

### Trois mots à retenir

- **Server-driven** : la vérité reste côté serveur, le client est une projection
- **Opt-in** : on ajoute `#[AsLiveComponent]` quand on en a besoin, pas avant
- **Continuité** : mêmes outils, mêmes patterns, même profiler que le reste de Symfony

---

## 🗣️ Narration (script oral)

> "Ce qui change tout avec Live Components, c'est qu'on **ne sort plus de l'écosystème Symfony** pour faire de l'interactivité. L'état du composant vit côté serveur, il est synchronisé automatiquement, et les actions sont de simples méthodes PHP. Pour un dev Symfony, c'est une continuité totale : mêmes outils, mêmes patterns, même debug avec le profiler.
>
> Concrètement, tu prends un Twig Component, tu changes l'attribut `#[AsTwigComponent]` en `#[AsLiveComponent]`, tu ajoutes le `DefaultActionTrait`, et tu marques les propriétés que tu veux synchroniser avec `#[LiveProp]`. Côté template, tu poses un `data-model` sur un input, et c'est tout. Tu n'écris **pas** de JavaScript. Tu n'écris **pas** d'endpoint. Tu n'écris **pas** de format JSON.
>
> Et côté utilisateur : ça réagit, ça met à jour le DOM intelligemment, ça préserve le focus et le scroll. C'est exactement la promesse de la famille HTML-over-the-wire — Livewire, Hotwire, LiveView, htmx — portée en idiomes Symfony."

---

## 🧭 Transition vers le chapitre 7

On a vu **ce qu'est** un Live Component, **comment il s'écrit** et **ce qu'il propose** comme features. Mais pour bien le maîtriser en tant que lead dev — pour debugger, optimiser, sécuriser — il faut comprendre **précisément ce qui se passe** entre le clic utilisateur et le DOM patché.

C'est l'objet du chapitre 7 : le **cycle de vie complet** d'un Live Component, étape par étape.
