# 🧱 5. Twig Components — Le socle

## 🎯 Objectif du chapitre

Comprendre **ce qu'est un Twig Component**, comment il s'écrit, et ce qu'il apporte concrètement.

---

## Slide 5.1 — Qu'est-ce qu'un Twig Component ?

### Définition

> **Une classe PHP + un template Twig = un composant réutilisable.**

### Analogie React

| React | Twig Component |
|-------|----------------|
| `class Alert extends Component` | `#[AsTwigComponent] class Alert` |
| `props` | propriétés publiques |
| `render()` | template `alert.html.twig` |
| `import Alert` | `<twig:Alert .../>` |

---

## Slide 5.2 — Structure d'un composant

### La classe PHP

```php
// src/Twig/Components/Alert.php
namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Alert
{
    public string $message;
    public string $type = 'info';
}
```

### Le template Twig associé

```twig
{# templates/components/Alert.html.twig #}
<div class="alert alert-{{ type }}">
    {{ message }}
</div>
```

### L'utilisation

```twig
<twig:Alert message="Commande validée" type="success" />

{# ou syntaxe classique #}
{{ component('Alert', { message: 'Oops', type: 'error' }) }}
```

---

## Slide 5.3 — Ce que ça apporte

- ✅ **Encapsulation** logique + template dans une unité claire
- ✅ **Réutilisabilité** plug-and-play
- ✅ **Autowiring** : injection de services dans le composant
- ✅ **API propre** : les props = propriétés publiques typées
- ✅ **Testabilité** : on teste la classe comme n'importe quel service

### Analogie React

> Les **props** = **propriétés publiques typées** de la classe PHP.

---

## Slide 5.4 — Bonus techniques

### Hooks de cycle de vie

```php
#[AsTwigComponent]
class ProductCard
{
    public Product $product;
    public string $title;

    #[PreMount]
    public function preMount(array $data): array
    {
        // normaliser / valider les props avant mount
        return $data;
    }

    #[PostMount]
    public function postMount(array $extra): array
    {
        // ajouter des attributs HTML, etc.
        return $extra;
    }
}
```

### Injection de services

```php
#[AsTwigComponent]
class UserAvatar
{
    public function __construct(
        private AvatarGenerator $generator,
    ) {}

    public User $user;

    public function url(): string
    {
        return $this->generator->urlFor($this->user);
    }
}
```

### Template dynamique

- Possibilité de choisir le template à l'exécution
- Utile pour des composants polymorphes (ex : `Button` → `button`/`link`)

---

## 💬 Message clé

> **"Twig Component = la brique atomique."**
> Avant même de parler d'interactivité, on gagne déjà **énormément** en structure et en maintenabilité.

---

## 🗣️ Narration (script oral)

> "Un Twig Component, c'est exactement ce qu'on cherchait depuis des années : une classe PHP qui porte le contrat d'entrée (les props), un template Twig qui porte le markup, et un couplage net entre les deux. On peut injecter des services, ajouter des méthodes de calcul, tester la classe en isolation. C'est un **design system vivant** dans ton app Symfony."

---

## 🧭 Transition vers le chapitre 6

Un Twig Component reste **statique** : il rend, et c'est fini. Pour l'interactivité, il faut passer à la couche suivante : **Live Components**.
