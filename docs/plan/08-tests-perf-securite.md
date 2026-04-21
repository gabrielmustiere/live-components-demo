# 🧪 8. Tests, performance & sécurité

## 🎯 Objectif du chapitre

Le code marche. On sait l'écrire. Place aux trois sujets qu'on retrouve **systématiquement** en code review et en prod :

1. **Comment teste-t-on** un Twig Component et un Live Component sans démarrer un navigateur ?
2. **Comment profile-t-on** un Live Component et quels sont les pièges de perf typiques ?
3. **Comment sécurise-t-on** un composant dont l'état voyage dans le DOM et peut être falsifié par le client ?

> ⚠️ **À retenir** : ces trois sujets ne sont **pas optionnels**. Un Live Component mal testé, non-profilé ou mal sécurisé est une dette qui se paie vite en prod — parce que chaque interaction utilisateur = une requête HTTP qui passe dans ton code.

---

## Slide 8.1 — Tester un Twig Component

### Le cas le plus simple : `renderTwigComponent`

Le bundle expose un trait `InteractsWithTwigComponents` (via `TwigComponentTestCase` ou composable dans un `KernelTestCase`).

```php
<?php

namespace App\Tests\Twig\Components;

use App\Enum\ButtonVariant;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

final class ButtonTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testRendersPrimaryVariant(): void
    {
        $rendered = $this->renderTwigComponent('Atom:Button', [
            'label'   => 'Acheter',
            'variant' => ButtonVariant::Primary,
        ]);

        self::assertStringContainsString('btn-primary', (string) $rendered);
        self::assertStringContainsString('Acheter', (string) $rendered);
    }

    public function testDisabledState(): void
    {
        $rendered = $this->renderTwigComponent('Atom:Button', [
            'label'    => 'Supprimer',
            'disabled' => true,
        ]);

        self::assertStringContainsString('disabled', (string) $rendered);
    }
}
```

### Tester uniquement le mount (sans rendu Twig)

Quand on veut vérifier la **logique de la classe** (PreMount, calculs, services) sans payer le coût du rendu :

```php
$component = $this->mountTwigComponent('UserAvatar', [
    'user' => $this->createUser('Alice'),
]);

self::assertSame('AL', $component->initials);
self::assertStringStartsWith('https://avatars/', $component->generatedUrl);
```

### Quand tester, quoi tester

| Cas | Stratégie |
|-----|-----------|
| Composant d'atomic design critique (Button, FormField, Alert) | **Test de rendu** avec quelques variantes |
| Composant avec `PreMount` / `PostMount` / services | **Test du mount** (vérifier les props normalisées) |
| Composant purement présentiel (layout, Card) | **Pas de test** — couvert par les tests fonctionnels des pages |
| Composant qui encapsule une règle métier | **Test unitaire** sur la méthode métier, pas via le rendu |

---

## Slide 8.2 — Tester un Live Component

### Le trait magique : `InteractsWithLiveComponents`

```php
<?php

namespace App\Tests\Twig\Components;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

final class ProductSearchTest extends KernelTestCase
{
    use InteractsWithLiveComponents;

    public function testSearchFiltersResults(): void
    {
        // 1. Instancier le composant (équivalent d'un render initial)
        $component = $this->createLiveComponent(
            name: 'ProductSearch',
            data: ['query' => ''],
        );

        // 2. Modifier une LiveProp — simule data-model côté client
        $component->set('query', 'clavier');

        // 3. Inspecter le composant PHP directement
        /** @var \App\Twig\Components\ProductSearch $instance */
        $instance = $component->component();
        self::assertCount(3, $instance->getResults());

        // 4. Inspecter le DOM re-rendu
        self::assertStringContainsString('Clavier mécanique', (string) $component->render());
    }

    public function testIncrementAction(): void
    {
        $component = $this->createLiveComponent('Counter', ['count' => 0]);

        // Appeler une LiveAction
        $component->call('increment');
        self::assertSame(1, $component->component()->count);

        $component->call('increment');
        $component->call('increment');
        self::assertSame(3, $component->component()->count);

        // Reset
        $component->call('reset');
        self::assertSame(0, $component->component()->count);
    }
}
```

### L'API du `TestLiveComponent`

| Méthode | Rôle |
|---------|------|
| `set(string $prop, mixed $value)` | Modifie une LiveProp writable (re-render implicite) |
| `call(string $action, array $args = [])` | Invoque une LiveAction avec arguments `#[LiveArg]` |
| `refresh()` | Force un re-render sans action |
| `emit(string $name, array $args = [])` | Émet un événement vers un parent |
| `component()` | Retourne **l'instance PHP courante** (après le dernier cycle) |
| `render()` | Retourne le HTML courant |
| `response()` | Retourne la `Response` brute (statuts, redirections) |

### Tester une action qui retourne une redirection

```php
public function testCheckoutRedirectsToSummary(): void
{
    $component = $this->createLiveComponent('CheckoutWizard', [
        'step'    => 'payment',
        'orderId' => 42,
    ]);

    $component->call('confirm');

    $response = $component->response();
    self::assertSame(200, $response->getStatusCode());
    self::assertStringContainsString('/orders/42/summary', (string) $response->headers->get('X-Live-Redirect'));
}
```

### Tester la validation

Avec `ValidatableComponentTrait`, on peut vérifier le comportement des contraintes :

```php
public function testEmailValidation(): void
{
    $component = $this->createLiveComponent('RegistrationForm', ['email' => 'not-an-email']);

    $component->call('submit');

    $instance = $component->component();
    self::assertTrue($instance->hasValidationErrors());
    self::assertStringContainsString('Email', (string) $component->render());
}
```

### Tests E2E : quand et comment

Pour les interactions qui impliquent **réellement le navigateur** (focus preserve, morphdom, data-loading), passer par **Panther** ou un test de contrôleur classique qui POST sur `/_components/*`. Mais dans 90 % des cas, `InteractsWithLiveComponents` suffit — c'est la même boucle instanciation / hydratation / action / rendu que le bundle exécute en prod.

---

## Slide 8.3 — Performance : les 3 pièges principaux

### 🔴 Piège 1 — Re-render qui rejoue un N+1

Chaque interaction = une requête = **toutes les queries Doctrine du re-render** sont rejouées. Un N+1 qui passe inaperçu à la page initiale devient une **cascade de requêtes par frappe clavier**.

```php
// ❌ Chaque re-render fait 1 query + N queries (produits) + N queries (tags)
public function getProducts(): array
{
    return $this->products->findFiltered($this->search);
    // dans le template : {% for p in this.products %}{{ p.tags|length }}{% endfor %}
}

// ✅ Une seule query avec join explicite
public function getProducts(): array
{
    return $this->products->findFilteredWithTags($this->search);
}
```

**Diagnostic** : ouvrir le profiler Symfony, filtrer sur `POST /_components/*`, onglet Doctrine. Si le nombre de queries croît avec le nombre de résultats affichés, c'est un N+1.

### 🔴 Piège 2 — Appel répété des méthodes de rendu

Rappel du chapitre 3 (Twig Components) :

```twig
{# ❌ this.results est appelé 2 fois → 2 requêtes SQL #}
{% if this.results is not empty %}
    <ul>{% for p in this.results %}...{% endfor %}</ul>
{% endif %}

{# ✅ computed.results mis en cache sur la durée du rendu #}
{% if computed.results is not empty %}
    <ul>{% for p in computed.results %}...{% endfor %}</ul>
{% endif %}
```

En Live Component, **chaque re-render repart de zéro** : le cache `computed.` ne survit pas entre deux cycles Ajax. Pour partager un calcul entre plusieurs rendus Ajax, il faut explicitement stocker le résultat — ou mieux, **recharger depuis une source cachée** (Doctrine 2nd-level cache, Symfony cache).

### 🔴 Piège 3 — Volume de requêtes réseau

Un input avec `data-model` sans debounce = **une requête par frappe**. À 5 caractères/seconde, c'est 5 req/s par utilisateur — qui rejouent firewall, security, bootstrap Doctrine, Twig render…

**Les bons outils** :

```twig
{# Recherche : debounce 300 ms #}
<input data-model="debounce(300)|query" type="search">

{# Select : re-render seulement au change #}
<select data-model="on(change)|sortBy">...</select>

{# Formulaire long : on maintient l'état, on attend la soumission #}
<input data-model="norender|draft" type="text">
```

### Profiler en pratique

```bash
# 1. Laisser le profiler ouvert sur la page du composant
# 2. Interagir (taper, cliquer)
# 3. Dans /_profiler, filtrer les requêtes par URL /_components/
# 4. Pour chaque cycle :
#    - Onglet Doctrine : nombre de queries, requêtes lentes
#    - Onglet Twig : temps de rendu, nombre de templates
#    - Onglet Performance : temps total, goulots
```

Pour **Blackfire** : profiler directement une `LiveAction` en ajoutant le header `X-Blackfire-Query` sur la requête. Recommandé pour les pages à trafic élevé.

### Ordre de grandeur sain vs alerte

| Métrique | 🟢 Sain | 🟠 À surveiller | 🔴 Alerte |
|----------|---------|-----------------|-----------|
| Temps re-render (local) | < 50 ms | 50–200 ms | > 200 ms |
| Queries Doctrine / cycle | 1–5 | 5–15 | > 15 |
| Taille payload sérialisé | < 5 KB | 5–30 KB | > 30 KB |
| Taille réponse HTML | < 30 KB | 30–100 KB | > 100 KB |

Un état `LiveProp` qui pèse > 30 KB dans le DOM = quasiment toujours une entité Doctrine complète sérialisée. Passer à un ID + recharger en `#[PostHydrate]`.

---

## Slide 8.4 — Sécurité : les invariants à tenir

### 🔑 Invariant 1 — Les `LiveProp writable` sont des entrées utilisateur

Le client **peut envoyer n'importe quelle valeur** dans le payload Ajax. Exactement comme un champ de formulaire : **valider systématiquement**.

```php
use Symfony\Component\Validator\Constraints as Assert;

#[LiveProp(writable: true)]
#[Assert\NotBlank]
#[Assert\Length(max: 120)]
public string $title = '';

#[LiveProp(writable: true)]
#[Assert\Range(min: 1, max: 100)]
public int $pageSize = 20;

#[LiveProp(writable: true)]
#[Assert\Choice(choices: ['name', 'price_asc', 'price_desc'])]
public string $sortBy = 'name';
```

**Le bundle fait confiance au type PHP** : si tu déclares `public int $pageSize`, le client ne peut pas envoyer une string. Mais il peut envoyer `999999999` → d'où `Assert\Range`.

### 🔑 Invariant 2 — Les `LiveProp` non-writable sont signées

Les props **sans `writable: true`** sont sérialisées dans le DOM **avec un fingerprint**. Si le client modifie la valeur dans le DOM et renvoie le payload altéré, le bundle **rejette la requête** (fingerprint mismatch).

```php
// Le client peut modifier cette valeur dans le DOM → elle repartira modifiée
#[LiveProp(writable: true)]
public string $query = '';

// Le client peut modifier le DOM mais le serveur rejettera le payload altéré
#[LiveProp]
public int $postId;
```

**Règle** : tout ce qui identifie une ressource côté serveur (un ID, un tenant, un UUID) doit rester **non-writable**.

### 🔑 Invariant 3 — Autoriser une `LiveAction` avec `#[IsGranted]`

Les `LiveAction` sont des méthodes PHP qui mutent l'état. Il faut les sécuriser **comme un contrôleur** :

```php
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsLiveComponent]
final class OrderEditor
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $orderId;

    #[LiveAction]
    #[IsGranted('EDIT', 'order')]
    public function save(OrderRepository $orders): void
    {
        $order = $this->getOrder();
        // ...
    }

    #[LiveAction]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAllItems(): void
    {
        // ...
    }

    #[ExposeInTemplate(name: 'order')]
    public function getOrder(): Order
    {
        return $this->orders->find($this->orderId) ?? throw new NotFoundHttpException();
    }
}
```

### 🔑 Invariant 4 — Ne jamais stocker de secret dans une `LiveProp`

**Tout** ce qui est dans une `LiveProp` finit dans le DOM (sérialisé en JSON en attribut `data-live-props-value`). Donc :

- ❌ Pas de `password`, `plainPassword`, `currentPassword`
- ❌ Pas de `token`, `apiKey`, `secret`
- ❌ Pas de `creditCardNumber`, `cvc`
- ❌ Pas de colonnes Doctrine sensibles exposées par erreur (une entité `User` sérialisée complète expose souvent le mot de passe hashé)

Règle pratique : pour les entités, **stocker l'ID**, recharger côté serveur :

```php
#[LiveProp]
public int $userId;

public function getUser(): User
{
    return $this->users->find($this->userId) ?? throw new NotFoundHttpException();
}
```

### 🔑 Invariant 5 — Valider les `#[LiveArg]` d'une action

```php
#[LiveAction]
public function addToCart(
    #[LiveArg] int $productId,
    #[LiveArg] int $quantity,
): void {
    // Le client envoie ce qu'il veut. À valider avant tout usage :
    if ($quantity < 1 || $quantity > 99) {
        throw new \InvalidArgumentException('Invalid quantity');
    }

    $product = $this->products->find($productId)
        ?? throw new NotFoundHttpException();

    $this->cart->add($product, $quantity);
}
```

### CSRF & same-origin

Le bundle utilise un **header `Accept` custom** (`application/vnd.live-component+html`) + le **same-origin policy** du navigateur. Cela suffit à bloquer les requêtes CSRF cross-site classiques. Pas de token CSRF explicite à gérer.

### Checklist de code review "sécurité Live Component"

- [ ] Chaque `LiveProp writable` a au moins une contrainte `Assert\*` adaptée (pour les scalaires pouvant venir du client)
- [ ] Chaque `LiveAction` sensible a un `#[IsGranted]`
- [ ] Aucune `LiveProp` ne stocke un secret ou une entité complète
- [ ] Chaque `#[LiveArg]` est validé avant usage (range, choice, existence)
- [ ] Les identifiants de ressource (IDs, UUIDs) sont **non-writable**
- [ ] Les erreurs métier dans une action sont **attrapées** et traduites en état (pas propagées en 500)

---

## 💬 Message clé

> **"Un Live Component, c'est un contrôleur déguisé."**
>
> Tout ce qui s'applique à un contrôleur Symfony s'applique à une `LiveAction` : validation, autorisation, logs, profiler. La seule différence, c'est que l'état voyage dans le DOM — donc **rien de sensible n'y a sa place**.

---

## 🗣️ Narration (script oral)

> "La bonne nouvelle avec les tests, c'est qu'un Live Component se teste comme une classe PHP. `createLiveComponent`, `set`, `call`, tu observes l'instance après le cycle. Pas de navigateur, pas de headless, pas de jsdom. Les tests tournent en PHPUnit, à la vitesse d'un test unitaire.
>
> Côté perf, les deux choses à regarder systématiquement sont le nombre de queries Doctrine par re-render — le N+1 passe vite inaperçu parce qu'on l'a pas à chaque page, mais à chaque frappe clavier — et la taille de l'état sérialisé dans le DOM, qui explose dès qu'on stocke une entité complète au lieu d'un ID.
>
> Côté sécu, le mental model à garder : tout ce qui est `writable` est une entrée utilisateur, tout ce qui est dans une `LiveProp` est exposé dans le DOM. Tu valides comme un form, tu autorises comme un controller, tu ne stockes jamais un secret. C'est du réflexe Symfony classique, appliqué à une classe qui se comporte comme un contrôleur persistant."

---

## 🧭 Transition vers le chapitre 9

On a le code, les tests, la perf et la sécu. Place à la **synthèse** : quand choisir quoi, quelles limites assumer, et qu'est-ce qu'on retient au final.
