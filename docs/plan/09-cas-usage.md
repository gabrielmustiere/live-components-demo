# 🧪 9. Cas d'usage concrets

## 🎯 Objectif du chapitre

Ancrer la théorie dans des **cas réels** — ce que tes équipes vont effectivement construire.

---

## Slide 9.1 — Quand utiliser Twig Components

### ✅ Cas typiques

- **UI statique réutilisable**
  - Boutons, badges, alerts, tooltips
  - Avatars, breadcrumbs, tags
- **Design system interne**
  - Cohérence visuelle sur tout le produit
  - Un seul endroit pour changer le style d'un bouton
- **Blocks de layout**
  - Header, sidebar, footer, navigation
- **Cards & listings**
  - Cards produit, cards utilisateur (sans interaction live)
- **Composants "data display"**
  - Tables, listings, fiches détails

### Exemples concrets

```twig
<twig:Button variant="primary" icon="plus" label="Nouvelle commande" />

<twig:ProductCard :product="product" :canEdit="is_granted('EDIT', product)" />

<twig:Alert type="warning">
    Votre session expire dans 5 minutes.
</twig:Alert>
```

---

## Slide 9.2 — Quand utiliser Live Components

### ✅ Cas typiques

- **Formulaires dynamiques**
  - Form multi-étapes (wizards)
  - Champs conditionnels (afficher "N° SIRET" si "type=entreprise")
  - Validation live par champ
- **Recherche / autocomplete**
  - Search-as-you-type
  - Suggestions en temps réel
- **Filtres & listings dynamiques**
  - Liste produit filtrable sans rechargement
  - Pagination fluide
- **Mini-actions contextuelles**
  - Compteur, like, bookmark
  - Quick edit inline
- **Panier / cart dynamique**
  - Ajout/retrait article
  - Mise à jour total en live

### Exemples concrets

```twig
{# Recherche live produit #}
<twig:ProductSearch />

{# Panier dynamique #}
<twig:Cart :userId="app.user.id" />

{# Form wizard #}
<twig:CheckoutWizard :step="1" />

{# Autocomplete #}
<twig:UserPicker name="assignee" />
```

---

## Slide 9.3 — Exemple détaillé : recherche live produit

### Côté PHP

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
        if (strlen($this->query) < 2) {
            return [];
        }

        return $this->products->search($this->query, limit: 10);
    }
}
```

### Côté Twig

```twig
<div>
    <input
        type="text"
        data-model="debounce(300)|query"
        placeholder="Rechercher un produit…"
    >

    <ul>
        {% for product in this.results %}
            <li>{{ product.name }} — {{ product.price }}€</li>
        {% else %}
            <li class="empty">Aucun résultat</li>
        {% endfor %}
    </ul>
</div>
```

### Ce qu'on obtient

- Recherche **full server-side** (pas de duplication PHP/JS)
- **Debounce** natif via `data-model="debounce(300)|query"`
- Aucun endpoint JSON à créer
- Testable comme n'importe quelle classe PHP

---

## Slide 9.4 — Exemple détaillé : compteur

### Cas d'école simple, parfait pour la démo

```php
#[AsLiveComponent]
class Counter
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
<div>
    <h2>Compteur : {{ count }}</h2>
    <button data-action="live#action" data-live-action-param="decrement">−</button>
    <button data-action="live#action" data-live-action-param="increment">+</button>
    <button data-action="live#action" data-live-action-param="reset">reset</button>
</div>
```

### Pourquoi c'est un excellent exemple

- **30 lignes** au total
- Montre **LiveProp + LiveAction + re-render**
- Zéro JS écrit à la main

---

## Slide 9.5 — Anti-patterns à éviter

### ❌ Live Component pour du markup purement statique

> Si ton composant n'a **ni props writable ni LiveAction**, c'est un Twig Component.

### ❌ Twig Component pour porter tout un formulaire métier lourd

> Tu vas finir par bricoler de l'Ajax / Stimulus custom. Passe en Live.

### ❌ LiveProp gigantesques

> Ne mets **pas une entité Doctrine complète** dans une `LiveProp`. Préfère l'ID + hydrate côté serveur.

### ❌ Chaînes d'actions qui enchaînent 5 round-trips

> Regroupe la logique dans une seule `LiveAction`. La latence réseau est ton ennemie.

---

## 💬 Message clé

> **"Le bon composant au bon endroit."**
> Twig pour la structure, Live pour l'interaction, et SPA uniquement où la latence réseau devient inacceptable.

---

## 🗣️ Narration (script oral)

> "Ces exemples ne sont pas des cas d'école : ce sont littéralement **90% de ce qu'on fait** dans un backoffice Symfony. Formulaires dynamiques, listings filtrables, quick actions… Tout ça, avant, demandait un mélange de Stimulus, d'Ajax custom, de JS maison. Aujourd'hui, c'est une classe PHP et un template Twig. La productivité qu'on gagne là-dessus est **vraiment significative**."

---

## 🧭 Transition vers le chapitre 10

Maintenant qu'on a vu les cas d'usage, soyons honnêtes : **avantages ET limites** de l'approche.
