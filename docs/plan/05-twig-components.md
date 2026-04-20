# 🧱 5. Twig Components — Le socle

## 🎯 Objectif du chapitre

Comprendre **ce qu'est un Twig Component**, comment il s'écrit, et ce qu'il apporte concrètement.

Après le chapitre 4 qui a posé la vision d'ensemble de Symfony UX, ce chapitre descend au niveau du code. On va répondre à cinq questions :

1. **Quelle est la structure** d'un Twig Component, fichier par fichier ?
2. **Comment fonctionnent les props** — contrat d'entrée, typage, valeurs par défaut ?
3. **Comment passe-t-on du contenu HTML** à l'intérieur d'un composant (slots) ?
4. **Comment un composant interagit-il** avec les services PHP et le rendu Twig ?
5. **Quels sont les pièges** à connaître avant d'utiliser ça en production ?

> ⚠️ **À retenir** : un Twig Component est **statique**. Il rend une fois, côté serveur, et c'est terminé. C'est son rôle — et c'est déjà énorme. La réactivité (état, interactions) arrive au chapitre 6 avec Live Components.

---

## Slide 5.1 — Qu'est-ce qu'un Twig Component ?

### Définition

> **Une classe PHP + un template Twig = un composant réutilisable.**

C'est la réponse directe aux **deux premières douleurs** identifiées au chapitre 2 :

| Douleur (ch. 2) | Réponse Twig Component |
|-----------------|------------------------|
| Pas de contrat de typage entre vue et données | → **Props = propriétés PHP typées** |
| Pas d'unité d'organisation naturelle | → **1 classe + 1 template = 1 dossier** |

La troisième douleur (réactivité server-driven) reste ouverte — c'est exactement ce que Live Components viendra combler.

### Analogie React

| React | Twig Component |
|-------|----------------|
| `class Alert extends Component` | `#[AsTwigComponent] class Alert` |
| `props` | propriétés publiques PHP typées |
| `render()` | template `Alert.html.twig` |
| `import Alert` puis `<Alert ... />` | `<twig:Alert ... />` |
| `children` | `{% block content %}{% endblock %}` |

### La syntaxe d'appel

```twig
{# Syntaxe HTML-like (recommandée) #}
<twig:Alert message="Commande validée" type="success" />

{# Avec contenu (slot) #}
<twig:Alert type="warning">
    Ton abonnement expire dans <strong>3 jours</strong>.
</twig:Alert>

{# Syntaxe classique Twig (toujours valide) #}
{{ component('Alert', { message: 'Oops', type: 'error' }) }}
```

---

## Slide 5.2 — Anatomie d'un composant

### Les deux fichiers

```
src/Twig/Components/Alert.php          ← la classe PHP (logique, props)
templates/components/Alert.html.twig   ← le template (markup)
```

La convention : le nom de la classe détermine le nom du template. Pas de configuration à écrire.

### La classe PHP

```php
<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Alert
{
    public string $message = '';
    public string $type = 'info';     // valeur par défaut
    public bool $dismissible = false;
}
```

### L'attribut `#[AsTwigComponent]` en détail

```php
#[AsTwigComponent(
    name: 'ui:Alert',                           // nom personnalisé (défaut : nom de classe)
    template: 'ui/alert.html.twig',             // template custom
    exposePublicProps: false,                   // exige le préfixe `this.` dans le template
    attributesVar: '_attrs',                    // renomme la variable `attributes` (rare)
)]
```

Le cas 99 % du temps : `#[AsTwigComponent]` sans argument — tout est résolu par convention.

### Le template associé

```twig
{# templates/components/Alert.html.twig #}
<div {{ attributes.defaults({class: 'alert alert-' ~ type}) }}>
    {% if dismissible %}
        <button type="button" class="alert-close" aria-label="Fermer">×</button>
    {% endif %}

    {{ message }}
</div>
```

### `{{ attributes }}` — la passerelle HTML

La variable `attributes` capture **tous les attributs HTML supplémentaires** passés au composant depuis le template appelant.

```twig
{# L'appelant peut passer n'importe quel attribut HTML #}
<twig:Alert message="OK" type="success" id="flash-1" data-testid="alert" />
```

```twig
{# Dans le template du composant — .defaults() fusionne les classes #}
<div {{ attributes.defaults({class: 'alert alert-' ~ type}) }}>
```

```html
<!-- Rendu final -->
<div class="alert alert-success" id="flash-1" data-testid="alert">OK</div>
```

Les méthodes utiles sur `attributes` :

```twig
{{ attributes.defaults({class: 'btn'}) }}    {# fusionne, ne remplace pas la classe #}
{{ attributes.only('class', 'id') }}         {# filtre, ne garde que ces attrs #}
{{ attributes.without('data-*') }}           {# exclut (wildcard supporté) #}
```

---

## Slide 5.3 — Props : le contrat d'entrée

### Propriétés publiques = props typées

```php
#[AsTwigComponent]
final class ProductCard
{
    public string $name = '';           // string avec défaut
    public int $price = 0;             // int
    public bool $inStock = true;       // bool
    public ?string $imageUrl = null;   // nullable
}
```

Dans le template, les props sont accessibles directement (sans `this.`) :

```twig
<div class="card">
    {% if imageUrl %}
        <img src="{{ imageUrl }}" alt="{{ name }}">
    {% endif %}
    <h3>{{ name }}</h3>
    <p>{{ price|number_format(2, ',', ' ') }} €</p>
    {% if not inStock %}
        <span class="badge badge-out">Rupture de stock</span>
    {% endif %}
</div>
```

### `#[ExposeInTemplate]` — exposer des propriétés non-publiques

Quand une propriété est `private` ou `protected` (injectée via constructeur, par exemple), on peut l'exposer dans le template sans la rendre publique :

```php
#[AsTwigComponent]
final class UserAvatar
{
    public User $user;

    #[ExposeInTemplate]
    private string $initials = '';

    #[ExposeInTemplate(name: 'avatarUrl')]  // alias dans le template
    private string $generatedUrl = '';

    public function __construct(private readonly AvatarGenerator $generator) {}

    #[PostMount]
    public function postMount(): void
    {
        $this->initials = strtoupper(substr($this->user->getName(), 0, 2));
        $this->generatedUrl = $this->generator->urlFor($this->user);
    }
}
```

```twig
{# initials et avatarUrl sont accessibles directement #}
<img src="{{ avatarUrl }}" alt="{{ initials }}">
```

### ⚠️ Piège : les booleans en string

```twig
{# ❌ Mauvais — la string 'false' est truthy en PHP/Twig #}
<twig:Alert dismissible="false" />

{# ✅ Correct — utiliser une expression Twig #}
<twig:Alert :dismissible="false" />
<twig:Alert dismissible="{{ false }}" />
```

Le préfixe `:` est un raccourci pour "évaluer comme expression Twig, pas comme string".

---

## Slide 5.4 — Slots : passer du contenu dans un composant

C'est le mécanisme pour faire l'équivalent des `children` React ou des `slots` Vue — passer un **bloc de contenu HTML** à l'intérieur d'un composant.

### Slot principal (`block content`)

```twig
{# templates/components/Card.html.twig #}
<div {{ attributes.defaults({class: 'card'}) }}>
    <div class="card-body">
        {% block content %}{% endblock %}
    </div>
</div>
```

```twig
{# Utilisation #}
<twig:Card class="card-featured">
    <h3>Mon produit</h3>
    <p>Description avec <strong>HTML</strong> et {{ variable_twig }}</p>
</twig:Card>
```

### Slots nommés (`twig:block`)

```twig
{# templates/components/Modal.html.twig #}
<div {{ attributes.defaults({class: 'modal'}) }}>
    <div class="modal-header">
        {% block header %}{% endblock %}
    </div>
    <div class="modal-body">
        {% block content %}{% endblock %}
    </div>
    <div class="modal-footer">
        {% block footer %}
            <button type="button">Fermer</button>  {# contenu par défaut #}
        {% endblock %}
    </div>
</div>
```

```twig
{# Utilisation avec blocs nommés #}
<twig:Modal id="confirm-dialog">
    <twig:block name="header">
        <h2>Confirmer la suppression</h2>
    </twig:block>

    Voulez-vous vraiment supprimer cet élément ?

    <twig:block name="footer">
        <button type="button">Annuler</button>
        <button type="submit" class="btn-danger">Supprimer</button>
    </twig:block>
</twig:Modal>
```

Le contenu entre les balises d'un composant — sans `twig:block` — atterrit dans le `block content`. Chaque slot nommé peut avoir un **contenu par défaut** dans le template du composant.

### Attributs imbriqués pour les slots

Pour passer des attributs HTML **vers un sous-élément** du composant :

```twig
{# Passage d'attributs vers le header interne de Modal #}
<twig:Modal header:class="bg-danger" header:id="modal-header">
    Contenu
</twig:Modal>
```

```twig
{# Dans le template du composant #}
<div {{ attributes }}>
    <div {{ attributes.nested('header') }}>  {# reçoit class="bg-danger" id="modal-header" #}
        {% block header %}{% endblock %}
    </div>
</div>
```

---

## Slide 5.5 — Services, méthodes calculées et `mount()`

### Injection de services

```php
#[AsTwigComponent]
final class RecentOrders
{
    public User $user;
    public int $limit = 5;

    public function __construct(
        private readonly OrderRepository $orders,
        private readonly Security $security,
    ) {}
}
```

### Méthodes calculées : `computed.` vs `this.`

Un composant peut exposer des **méthodes calculées** — leur résultat est accessible dans le template via `this.methodName()` ou `computed.methodName`.

La différence est importante :

```twig
{# ❌ this.results — la méthode est appelée à chaque accès dans le template #}
{% for item in this.results %}...{% endfor %}
{# Si on accède this.results une deuxième fois → deuxième appel SQL #}

{# ✅ computed.results — résultat mis en cache pour tout le rendu #}
{% for item in computed.results %}...{% endfor %}
{# Deuxième accès → retourne le cache, pas de deuxième requête #}
```

```php
#[AsTwigComponent]
final class RecentOrders
{
    public User $user;
    public int $limit = 5;

    public function __construct(private readonly OrderRepository $orders) {}

    public function results(): array
    {
        return $this->orders->findRecentFor($this->user, $this->limit);
    }
}
```

```twig
{# templates/components/RecentOrders.html.twig #}
<ul>
    {% for order in computed.results %}
        <li>{{ order.reference }} — {{ order.total }} €</li>
    {% endfor %}
</ul>
```

### La méthode `mount()`

`mount()` est appelée **juste après l'instantiation**, avec les props passées en paramètres. Utile pour une initialisation complexe qui ne rentre pas dans `PreMount`/`PostMount` :

```php
#[AsTwigComponent]
final class DateRangePicker
{
    public \DateTimeImmutable $start;
    public \DateTimeImmutable $end;

    public function mount(
        string|\DateTimeImmutable $start,
        string|\DateTimeImmutable $end,
    ): void {
        // Normalise strings ou DateTimeImmutable en DateTimeImmutable
        $this->start = is_string($start) ? new \DateTimeImmutable($start) : $start;
        $this->end   = is_string($end)   ? new \DateTimeImmutable($end)   : $end;
    }
}
```

```twig
{# Les deux formes sont acceptées #}
<twig:DateRangePicker start="2024-01-01" end="2024-03-31" />
<twig:DateRangePicker :start="order.createdAt" :end="order.deliveredAt" />
```

---

## Slide 5.6 — Cycle de vie : PreMount, PostMount, template dynamique

### `#[PreMount]` — valider et normaliser les props avant assignation

Appelé **avant** que les propriétés soient assignées. Reçoit le tableau de données brutes, retourne le tableau modifié.

```php
#[AsTwigComponent]
final class Alert
{
    public string $message = '';
    public string $type = 'info';

    #[PreMount]
    public function preMount(array $data): array
    {
        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $resolver->setIgnoreUndefined(true);
        $resolver->setDefaults(['type' => 'info']);
        $resolver->setAllowedValues('type', ['info', 'success', 'warning', 'danger']);
        $resolver->setRequired('message');

        return $resolver->resolve($data) + $data;
    }
}
```

### `#[PostMount]` — logique post-assignation

Appelé **après** que les props ont été assignées. Reçoit les données restantes non consommées (attributs HTML extra).

```php
#[AsTwigComponent]
final class Button
{
    public string $label = '';
    public string $variant = 'primary';
    public bool $loading = false;

    #[PostMount]
    public function postMount(array $data): array
    {
        // 'loading' est converti en 'disabled' pour l'HTML
        if ($this->loading) {
            $data['disabled'] = true;
        }
        return $data;
    }
}
```

Plusieurs hooks peuvent coexister, ordonnés par priorité (`#[PreMount(priority: 10)]`).

### Template dynamique

Pour choisir le template à l'exécution — utile pour des composants polymorphes :

```php
use Symfony\UX\TwigComponent\Attribute\FromMethod;

#[AsTwigComponent(template: new FromMethod('getTemplateName'))]
final class Button
{
    public string $tag = 'button'; // 'button' | 'a' | 'submit'

    public function getTemplateName(): string
    {
        return match ($this->tag) {
            'a'      => 'components/Button/link.html.twig',
            'submit' => 'components/Button/submit.html.twig',
            default  => 'components/Button/button.html.twig',
        };
    }
}
```

---

## Slide 5.7 — Composants anonymes

Un composant qui n'a besoin **d'aucune logique PHP** peut vivre dans un template seul — pas de classe PHP requise.

### Le cas minimal

```twig
{# templates/components/Badge.html.twig #}
{% props color = 'gray', label %}

<span {{ attributes.defaults({class: 'badge badge-' ~ color}) }}>
    {{ label }}
</span>
```

```twig
{# Utilisation — exactement comme un composant PHP #}
<twig:Badge label="Nouveau" color="green" />
<twig:Badge label="{{ product.status.label }}" :color="product.status.color" />
```

`{% props %}` déclare les propriétés attendues avec leurs valeurs par défaut — c'est l'équivalent Twig du `public string $color = 'gray'`.

### Convention de nommage namespace

```
templates/components/Button/Primary.html.twig  → <twig:Button:Primary />
templates/components/Menu/index.html.twig       → <twig:Menu />
```

### Quand utiliser

- Primitives UI sans logique : Badge, Divider, Icon, Spinner
- Composants qui ne font que du markup avec des props simples
- Prototype rapide avant de passer à une classe PHP si le besoin grandit

---

## Slide 5.8 — Gotchas à connaître

### 1. Boolean props et strings

Déjà vu en slide 5.3, mais à répéter : toujours utiliser `:prop` ou `{{ expression }}` pour passer des booleans.

```twig
<twig:Alert :dismissible="false" />     {# ✅ false PHP #}
<twig:Alert dismissible="false" />      {# ❌ string 'false' → true #}
```

### 2. `readonly` classes et propriétés

PHP `readonly` interdit l'assignation post-construction — incompatible avec le mécanisme d'assignation de props :

```php
// ❌ Plante : readonly empêche l'assignation des props
#[AsTwigComponent]
final readonly class UserProfile { ... }

// ✅ Les services injectés peuvent être readonly, pas les props
#[AsTwigComponent]
final class UserProfile
{
    public User $user;  // prop normale

    public function __construct(
        private readonly UserRepository $users,  // service readonly : OK
    ) {}
}
```

### 3. `computed.` uniquement pour les méthodes sans argument

La mise en cache de `computed.` repose sur le nom de la méthode. Une méthode avec paramètres ne peut pas être cachée de cette façon — utiliser `this.method(arg)` et gérer le cache manuellement si besoin.

### 4. Un Twig Component n'a pas d'état entre deux rendus

Chaque `<twig:MonComposant />` est une **instance fraîche** à chaque rendu de la page. Aucune donnée ne persiste entre deux affichages. Si tu as besoin de persistance ou de réactivité → Live Components (chapitre 6).

### 5. `{{ attributes }}` doit être sur la racine

Si le template du composant n'utilise pas `{{ attributes }}`, les attributs HTML passés depuis l'appelant (`id`, `class`, `data-*`) sont **silencieusement ignorés**. Bonne pratique : toujours mettre `{{ attributes }}` ou `{{ attributes.defaults(...) }}` sur l'élément racine.

---

## Slide 5.9 — Récap : les douleurs du chapitre 2 répondues

Reprenons la grille des cinq critères du chapitre 2 :

| Critère | Twig Components |
|---------|:---------------:|
| **Encapsulation logique** | ✅ Classe PHP avec services injectables |
| **Template séparé** | ✅ `Alert.html.twig` dédié, colocalisé |
| **Props typées** | ✅ Propriétés PHP typées + `#[ExposeInTemplate]` |
| **État interne** | ❌ Aucun — instance fraîche à chaque rendu |
| **Performance** | ✅ Dans la même requête HTTP, aucun overhead |
| **Interactivité** | ❌ Rendu statique uniquement |

Deux cases restent vides — c'est **volontaire**. Twig Components résout les douleurs 1 et 2 du chapitre 2 (typage, organisation). Il ne prétend pas résoudre la troisième (réactivité) : c'est l'objet de Live Components.

---

## 💬 Message clé

> **"Twig Component = la brique atomique."**
> Avant même de parler d'interactivité, on gagne **énormément** en structure et en maintenabilité : un contrat d'entrée explicite, un template dédié, des services injectables, des slots pour la composition.

### Trois mots à retenir

- **Contrat** : les props typées remplacent les tableaux associatifs opaques
- **Colocation** : la classe PHP et le template vivent ensemble, on sait toujours où chercher
- **Composition** : les slots permettent de construire des composants qui enveloppent d'autres composants

---

## 🗣️ Narration (script oral)

> "Un Twig Component, c'est exactement ce qu'on cherchait depuis les `include` et les macros du chapitre 2. Une classe PHP qui porte le contrat d'entrée avec des propriétés typées, un template Twig qui porte le markup, et un couplage net entre les deux. On peut injecter des services dans le constructeur, écrire des méthodes calculées qui seront mises en cache pendant le rendu, valider les props avec PreMount, et composer des composants entre eux via les slots.
>
> Ce qui change vraiment par rapport à l'include ou à la macro, c'est le **contrat** : quand on écrit `<twig:Alert type="success" message="OK" />`, l'IDE sait exactement quelles propriétés existent, avec quels types, avec quelles valeurs par défaut. Si on passe une propriété qui n'existe pas, le bundle le dit. C'est du code qu'on peut refactorer en confiance.
>
> Et comme c'est 100 % Symfony-natif, on obtient le profiler, le var-dumper, le débug toolbar — tout ce qu'on connaît déjà. On n'apprend pas de nouvel outillage, on **étend** celui qu'on utilise."

---

## 🧭 Transition vers le chapitre 6

Un Twig Component reste **statique** : il rend une fois, et c'est terminé. Pour l'interactivité — un filtre qui se met à jour, un compteur, un formulaire qui valide à la frappe — il faut passer à la couche suivante.

Live Components prend exactement cette base (classe PHP + template Twig + props typées), et y ajoute deux choses : un **état synchronisé** entre client et serveur, et des **actions déclenchables depuis le DOM**. Sans écrire une ligne de JavaScript.
