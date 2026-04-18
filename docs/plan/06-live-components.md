# ⚡ 6. Live Components — Le game changer

## 🎯 Objectif du chapitre

Comprendre **ce qui rend un composant "Live"**, et pourquoi c'est un vrai changement de jeu côté Symfony.

---

## Slide 6.1 — Le problème restant

### Rappel

Un **Twig Component** est **statique** :

- Il est rendu une fois côté serveur
- Aucune interaction utilisateur ne le met à jour
- Pour l'interactivité → il fallait écrire du **Stimulus / Ajax / JS custom**

### Conséquence

- Duplication logique métier (PHP côté serveur + JS côté client)
- Friction DX : un composant "vivant" = plusieurs fichiers hétérogènes

---

## Slide 6.2 — Live Components = Twig + réactivité

### Définition

> **Un Twig Component qui se met à jour automatiquement via Ajax.**

L'utilisateur interagit, le composant **ré-exécute son rendu côté serveur**, et le DOM est patché côté client — **sans écrire de JS**.

---

## Slide 6.3 — Comment ça marche (flow)

```
   ┌─────────────┐
   │   User      │  clic / input / submit
   └──────┬──────┘
          ▼
   ┌─────────────┐
   │  Ajax call  │  (payload = état du composant)
   └──────┬──────┘
          ▼
   ┌─────────────┐
   │   PHP       │  hydrate → exécute action → re-render
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

- **Pas de SPA**
- **Pas de React / Vue**
- **Pas d'API JSON custom** à maintenir
- Le rendu **reste du Twig**

---

## Slide 6.4 — Inspirations

Live Components s'inscrit dans une **famille d'approches "HTML-over-the-wire"** :

- **Laravel Livewire** (PHP)
- **Phoenix LiveView** (Elixir)
- **Hotwire / Turbo** (Rails)
- **htmx** (indépendant)

### Philosophie commune

> **"Server-driven UI"** : l'état de vérité vit côté serveur, le client se contente de rendre.

---

## Slide 6.5 — Concepts clés

### `LiveProp` — état synchronisé

```php
#[AsLiveComponent]
class ProductSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';
}
```

- Synchronisé entre client ↔ serveur
- `writable: true` autorise la modification côté client
- Peut être hydraté/déshydraté automatiquement

### `LiveAction` — actions serveur

```php
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

Côté Twig :

```twig
<button data-action="live#action" data-live-action-param="increment">
    +1
</button>
<p>Compteur : {{ count }}</p>
```

### Re-render automatique

- Toute modification d'une `LiveProp`
- Toute `LiveAction` exécutée
- → déclenche un re-render du composant côté serveur

---

## 💬 Message clé

> **"Live Components rendent l'interactivité disponible sans quitter PHP/Twig."**
> On obtient 80% des bénéfices d'un SPA avec 20% de la complexité.

---

## 🗣️ Narration (script oral)

> "Ce qui change tout avec Live Components, c'est qu'on **ne sort plus de l'écosystème Symfony** pour faire de l'interactivité. L'état du composant vit côté serveur, il est synchronisé automatiquement, et les actions sont de simples méthodes PHP. Pour un dev Symfony, c'est une continuité totale : mêmes outils, mêmes patterns, même debug. Et côté utilisateur : ça réagit."

---

## 🧭 Transition vers le chapitre 7

Pour bien maîtriser Live Components en tant que lead dev, il faut comprendre **le cycle de vie précis** d'un composant.
