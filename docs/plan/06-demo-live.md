# 🧑‍💻 6. Démo live

## 🎯 Objectif du chapitre

On vient de voir **le modèle** (ch. 3–4) et **le moteur** (ch. 5). Avant d'attaquer les cas d'usage concrets, on **ancre** tout ça dans deux démos courtes et percutantes pour rendre le concept tangible.

> 💡 **Pourquoi placer la démo ici ?** Parce que l'audience a maintenant le vocabulaire (`LiveProp`, `LiveAction`, cycle, morphdom) pour **lire** le code en direct. Après la démo, les cas d'usage (ch. 7) s'analysent sur du tangible, pas de l'abstrait.

---

## Slide 6.1 — Le plan de démo

Deux démos, chacune **~3 minutes** :

1. **Twig Component** : une `Alert` + une `ProductCard`
2. **Live Component** : un compteur + une recherche live

Objectif : montrer **le saut qualitatif** entre les deux mondes.

---

## Démo 1 — Twig Component

### Étape 1 : le composant `Alert`

```php
// src/Twig/Components/Alert.php
namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Alert
{
    public string $message;
    public string $type = 'info';
    public bool $dismissible = false;
}
```

```twig
{# templates/components/Alert.html.twig #}
<div class="alert alert-{{ type }} {{ dismissible ? 'alert-dismissible' : '' }}">
    <p>{{ message }}</p>
    {% if dismissible %}
        <button class="close">×</button>
    {% endif %}
</div>
```

### Utilisation dans une page

```twig
<twig:Alert message="Bienvenue !" type="success" />

<twig:Alert
    message="Votre session expire bientôt"
    type="warning"
    :dismissible="true"
/>
```

### Points à souligner pendant la démo

- Props **typées en PHP**
- Syntaxe `<twig:Alert />` lisible et proche de JSX
- Autocomplétion IDE sur les props
- On peut **injecter un service** dans la classe si besoin

---

### Étape 2 : composant `ProductCard` avec service

```php
#[AsTwigComponent]
class ProductCard
{
    public function __construct(
        private PriceFormatter $formatter,
    ) {}

    public Product $product;
    public bool $showBadge = true;

    public function formattedPrice(): string
    {
        return $this->formatter->format($this->product->getPrice());
    }
}
```

```twig
<article class="product-card">
    <h3>{{ product.name }}</h3>
    <p class="price">{{ this.formattedPrice }}</p>
    {% if showBadge and product.isNew %}
        <span class="badge">Nouveau</span>
    {% endif %}
</article>
```

### Punchline de la démo 1

> **"Un composant statique = une classe + un template. Aucune nouveauté conceptuelle, mais une organisation radicalement meilleure."**

---

## Démo 2 — Live Component

### Étape 1 : le compteur

```php
// src/Twig/Components/Counter.php
namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class Counter
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $count = 0;

    #[LiveAction]
    public function increment(): void
    {
        $this->count++;
    }
}
```

```twig
<div{{ attributes }}>
    <h2>Compteur : {{ count }}</h2>
    <button
        data-action="live#action"
        data-live-action-param="increment"
    >+1</button>
</div>
```

### Ce qu'on montre pendant la démo

1. Clic sur `+1` → le chiffre change **sans rechargement**
2. Onglet **Network** : on voit l'Ajax partir, la réponse HTML arriver
3. Le DOM est **patché**, pas remplacé (focus, scroll préservés)
4. Zéro ligne de JS écrite

---

### Étape 2 : recherche live produit

```php
#[AsLiveComponent]
class ProductSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private ProductRepository $products,
    ) {}

    public function getResults(): array
    {
        return strlen($this->query) >= 2
            ? $this->products->search($this->query)
            : [];
    }
}
```

```twig
<div{{ attributes }}>
    <input
        type="search"
        data-model="debounce(300)|query"
        placeholder="Rechercher…"
    >

    <ul>
        {% for product in this.results %}
            <li>{{ product.name }}</li>
        {% endfor %}
    </ul>
</div>
```

### Ce qu'on montre pendant la démo

1. On tape → la liste se met à jour en **direct** (avec debounce)
2. On ouvre le code : **aucune ligne de JS custom**
3. La recherche utilise **directement le repository Doctrine**
4. On peut poser un breakpoint PHP dans `getResults()` et **débugger normalement**

---

## Slide 6.3 — Ce qu'on veut que le public retienne

Après ces deux démos :

- ✅ **"Un Twig Component, c'est simple et immédiatement utile"**
- ✅ **"Un Live Component, c'est du Twig qui devient réactif gratuitement"**
- ✅ **"Je peux commencer à l'utiliser demain dans mon app existante"**

---

## 🗣️ Narration (script oral)

> "Je vais volontairement choisir des démos **très simples**. Pas pour sous-estimer votre niveau, mais parce que la vraie surprise, c'est de réaliser à quel point c'est court. Une recherche live, c'est **une classe PHP de 15 lignes et un template de 10 lignes**. Quand vous le voyez fonctionner en direct, sans JS, vous comprenez pourquoi on qualifie Live Components de *game changer* pour l'écosystème Symfony."

---

## 💡 Conseils pour la démo en vrai

- **Démarrer le serveur dev avant la conf** (Symfony CLI)
- Avoir les **devtools ouverts sur Network** pour montrer les requêtes
- Préparer un **fallback** (screencast) en cas de coup dur réseau
- Montrer le **profiler Symfony** : les Live actions y apparaissent comme des requêtes classiques

---

## 🧭 Transition vers le chapitre 7

Le concept est ancré, les attributs `data-*` ne sont plus abstraits. Passons maintenant aux **cas d'usage concrets** : recherche, formulaires conditionnels, listes filtrables, quick actions — les patterns qu'on retrouve dans 90 % des backoffices Symfony.
