# 🧠 1. Problème & contexte

## 🎯 Objectif du chapitre

Poser le **problème initial** côté Symfony, puis faire l'**archéologie** des solutions qu'on a utilisées jusqu'ici pour composer de l'UI côté serveur. On examine chaque approche, ses **cas d'usage** et ses **limites concrètes**, pour comprendre **pourquoi aucune ne suffit** à fournir un vrai modèle composant.

> ⚠️ **À retenir** : ces techniques **ne sont pas mortes**. Elles restent utiles pour des cas précis. Mais aucune, seule, ne remplace un modèle composant de première classe.

---

## Slide 1.1 — Le problème initial

### Constat

- **Complexité croissante** des templates Twig sur les projets matures
- **Duplication de markup** : cards, alerts, modals, forms répétés partout
- **Difficulté à maintenir / tester / réutiliser** les blocs d'UI
- **Mélange logique métier / présentation** dans les templates

### Symptômes concrets

- Un `alert.html.twig` copié-collé dans 15 templates
- Des `if` imbriqués dans le Twig pour gérer les variantes
- Pas de contrat clair sur les "props" attendues
- Régressions visuelles à chaque refacto

### 💬 Message clé

> **"Avant, Twig = templates statiques + duplication + logique dispersée"**

On avait un langage de template puissant, mais **pas de modèle de composant**.

---

## 🧭 Slide 1.2 — La grille d'évaluation

Pour comparer objectivement les approches historiques, on utilise **cinq critères** récurrents dans tout le chapitre :

| Critère | Question posée |
|---------|----------------|
| **Encapsulation logique** | Peut-on mettre du code PHP métier associé à ce bloc d'UI ? |
| **Template séparé** | Le markup vit-il dans un fichier dédié (vs concaténé en PHP) ? |
| **Contrat de props typé** | Les entrées sont-elles validées, autocomplétées par l'IDE ? |
| **État interne** | Le "composant" peut-il avoir un state propre entre deux rendus ? |
| **Performance** | Le rendu est-il dans la même requête HTTP, sans overhead ? |

On va noter chaque solution sur ces axes pour bâtir le tableau récapitulatif final.

---

## Slide 1.3 — Twig `include`

### Exemple minimal

```twig
{# templates/components/_alert.html.twig #}
<div class="alert alert-{{ type|default('info') }}">
    {{ message }}
</div>
```

```twig
{# Utilisation #}
{{ include('components/_alert.html.twig', { type: 'error', message: 'Oops' }) }}
```

### Variante avec `with` et `only`

```twig
{# Isolation du contexte parent (seules les vars listées sont accessibles) #}
{{ include('components/_alert.html.twig', { type: 'warning', message: msg }, with_context = false) }}

{# Forme courte équivalente #}
{% include 'components/_alert.html.twig' with { type: 'warning' } only %}
```

### Ce que ça résout

- ✅ Factorisation du **markup répété** (la div, les classes CSS)
- ✅ Un seul endroit à modifier pour changer le design
- ✅ Template séparé, lisible, testable visuellement

### Ce que ça ne résout PAS

- ❌ **Aucune logique encapsulée côté PHP** : le template ne sait rien "faire"
- ❌ **Pas de contrat fort sur les props** : `type`, `message` sont juste des clés de tableau
  - Aucune validation, aucune erreur si tu passes `typ` au lieu de `type`
  - Aucune autocomplétion IDE
  - Aucune indication sur les types attendus (string ? enum ? array ?)
- ❌ **Pas d'état interne** : l'include est sans mémoire, pas de "state"
- ❌ **Héritage de contexte insidieux** : si tu oublies `only`, toutes les variables du template parent fuitent dans l'include — source de bugs silencieux

### Cas d'usage légitimes (aujourd'hui encore)

- Fragments purement visuels et sans logique (en-tête, pied de page, snippet statique)
- Réutilisation **locale** à un seul template ou groupe de templates

---

## Slide 1.4 — Twig `macro`

Les macros sont le **niveau au-dessus** des includes : elles ressemblent à des fonctions Twig avec paramètres nommés.

### Exemple

```twig
{# templates/macros/ui.html.twig #}
{% macro button(label, variant = 'primary', type = 'button') %}
    <button type="{{ type }}" class="btn btn-{{ variant }}">
        {{ label }}
    </button>
{% endmacro %}
```

```twig
{# Utilisation #}
{% import 'macros/ui.html.twig' as ui %}
{{ ui.button('Acheter', 'success') }}
{{ ui.button('Annuler', 'secondary', 'reset') }}
```

### Ce que ça améliore par rapport à `include`

- ✅ **Signature explicite** avec paramètres positionnels et valeurs par défaut
- ✅ **Isolation totale du contexte** par défaut (contrairement à include)
- ✅ Un peu plus proche de la notion de "composant appelable"

### Ce que ça ne résout toujours pas

- ❌ **Aucune logique PHP** associée (toujours 100 % Twig)
- ❌ **Pas de types** : les paramètres restent non typés
- ❌ **Pas d'état** : une macro est une fonction pure stateless
- ❌ **Pas de services** injectables : impossible d'appeler `translator`, `router` proprement depuis la logique du composant (il faudrait les passer à chaque appel)
- ❌ **Composition lourde** : imbriquer des macros qui appellent des macros devient illisible
- ❌ **Pas d'accès aux slots** : on ne peut pas passer un **bloc de contenu** Twig à une macro (on est limité aux strings en paramètres)

### Verdict

Les macros sont un **progrès** mais restent dans le monde Twig pur. Elles sont parfaites pour des primitives d'UI sans logique (un bouton, un label, un badge) et mal adaptées dès qu'il y a un peu de comportement métier.

---

## Slide 1.5 — `render(controller(...))` (ESI / sous-requête)

C'est la solution historique pour avoir un **"composant avec logique PHP"** : déléguer le rendu à un contrôleur dédié.

### Exemple

```twig
{# Dans le layout principal #}
{{ render(controller('App\\Controller\\CartController::miniCart')) }}
```

```php
// src/Controller/CartController.php
final class CartController extends AbstractController
{
    public function miniCart(CartRepository $carts, Security $security): Response
    {
        $cart = $carts->findCurrentFor($security->getUser());

        return $this->render('cart/_mini_cart.html.twig', [
            'itemCount' => $cart->count(),
            'total' => $cart->total(),
        ]);
    }
}
```

### Variantes

- `render_esi(...)` : délègue à un reverse proxy (Varnish) pour mise en cache indépendante
- `render_hinclude(...)` : le navigateur fait la requête via JavaScript

### Ce que ça résout

- ✅ **Encapsulation logique complète** : un vrai contrôleur avec DI, services, repositories
- ✅ **Template séparé**, props issues du contrôleur
- ✅ **Isolation** : le fragment vit dans sa propre sous-requête
- ✅ **Cache indépendant** possible (ESI) : le fragment peut avoir une TTL différente de la page

### Ce que ça ne résout PAS (et qui coûte cher)

- ❌ **Sous-requête HTTP interne** à chaque rendu
  - Nouveau cycle `Request → Kernel → Router → Controller → Response`
  - **Event subscribers** rejoués (security, locale, firewall…)
  - **Session** réouverte, **doctrine** potentiellement reconfiguré
- ❌ **Coût non trivial** sur une page avec 10–15 fragments : temps de réponse qui explose
- ❌ **Couplage fort au cycle HTTP** : l'état de request/session fuit dans le fragment
- ❌ **DX médiocre** : chaque "composant" = une route OU une action de contrôleur + un template, disséminés dans deux dossiers différents
- ❌ **Pas de props typées à l'appel** : on passe des scalaires dans `controller('...', {...})`, pas d'objet métier
- ❌ **Pas d'état côté fragment** : chaque sous-requête repart de zéro

### Cas d'usage légitimes (aujourd'hui)

- Fragments avec **politique de cache différente** de la page principale (ESI + Varnish)
- Fragments chargés **en lazy** côté client via `render_hinclude`

---

## Slide 1.6 — Twig functions / extensions custom

Quand on veut **une fonction Twig qui génère du HTML**, on écrit une extension.

### Exemple naïf (à éviter)

```php
// src/Twig/UiExtension.php
final class UiExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('badge', [$this, 'renderBadge'], ['is_safe' => ['html']]),
        ];
    }

    public function renderBadge(string $label, string $color = 'gray'): string
    {
        return sprintf(
            '<span class="badge badge-%s">%s</span>',
            htmlspecialchars($color, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
        );
    }
}
```

```twig
{# Utilisation #}
{{ badge(user.status, 'success') }}
```

### Variante moins pire — déléguer à un sous-template

```php
public function renderBadge(Environment $twig, string $label, string $color = 'gray'): string
{
    return $twig->render('ui/_badge.html.twig', [
        'label' => $label,
        'color' => $color,
    ]);
}
```

```php
// Déclaration
new TwigFunction('badge', [$this, 'renderBadge'], [
    'needs_environment' => true,
    'is_safe' => ['html'],
]),
```

### Ce que ça résout

- ✅ **Logique PHP** disponible (services injectables, autowiring)
- ✅ Testabilité PHP standard (PHPUnit sur l'extension)
- ✅ Appel très court côté Twig : `{{ badge(...) }}`

### Ce que ça ne résout PAS

- ❌ **HTML en string PHP** (version naïve) : illisible, difficile à maintenir, XSS facile si on oublie `htmlspecialchars`
- ❌ **Pas de slots** : on ne peut pas passer un bloc Twig en paramètre
- ❌ **Pas de props typées côté Twig** : un paramètre `color` reste une string arbitraire
- ❌ **Pas d'état** : fonction pure
- ❌ **Friction** : pour chaque composant, on crée une nouvelle fonction globale → explosion du namespace Twig
- ❌ **Pas d'interaction** : aucune mécanique pour gérer un événement utilisateur

### Cas d'usage légitimes

- **Helpers** purs (formater une date métier, générer un slug, convertir une enum en label traduit) — **pas** des composants d'UI

---

## Slide 1.7 — Form Type custom comme "composant"

Dans certains projets matures, on détournait **le composant Form** de Symfony pour faire office de "widget riche" (ex. un picker d'entité, un tag input, un date range).

### Exemple

```php
final class TagPickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [],
            'multiple' => true,
            'attr' => ['class' => 'tag-picker'],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
```

```twig
{# Thème Twig associé, avec block personnalisé #}
{% block tag_picker_widget %}
    <div class="tag-picker">
        {# markup custom… #}
    </div>
{% endblock %}
```

### Ce que ça résout

- ✅ Contrat de props via `OptionsResolver` (validation)
- ✅ Logique PHP encapsulée
- ✅ Template Twig dédié via le `form_theme`

### Ce que ça ne résout PAS

- ❌ **Uniquement pour des champs de formulaire** — inutilisable pour un `Card`, un `Modal`, un `Sidebar`
- ❌ **Overhead conceptuel** : tu réutilises une machinerie conçue pour la collecte de données pour faire de l'affichage
- ❌ **Pas d'interactivité** sans ajouter du JS custom par-dessus
- ❌ **Couplage fort** au cycle de vie du formulaire (bind, submit, validate…)

### Verdict

**Détournement** qui marche dans un cas très spécifique, mais qui n'est **pas un modèle composant généraliste**.

---

## Slide 1.8 — Template + sprinkle de JS (jQuery / Stimulus)

L'approche classique pour "rendre un template interactif" : générer le HTML côté serveur, puis attacher du comportement en JS.

### Exemple

```twig
<div data-controller="search"
     data-search-url-value="{{ path('app_search') }}">
    <input type="text" data-search-target="input" data-action="input->search#query">
    <ul data-search-target="results"></ul>
</div>
```

```js
// assets/controllers/search_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'results'];
    static values = { url: String };

    async query() {
        const r = await fetch(`${this.urlValue}?q=${this.inputTarget.value}`);
        this.resultsTarget.innerHTML = await r.text();
    }
}
```

### Ce que ça résout

- ✅ **Interactivité réelle** côté client (c'est le seul des précédents à le permettre)
- ✅ Séparation claire `markup` / `comportement`
- ✅ Excellente DX Stimulus quand on reste dans ce rôle

### Ce que ça ne résout PAS

- ❌ **Double codebase** : la logique métier se retrouve souvent **dupliquée** (validation PHP + validation JS)
- ❌ **Synchronisation de l'état** : l'état client (formulaire en cours de saisie) et l'état serveur (vérité de la DB) divergent
- ❌ **Pas de composant PHP** : aucune classe PHP ne modélise ce bloc d'UI, seulement un Stimulus controller
- ❌ **Un endpoint ad hoc par interaction** : chaque comportement = une route + un contrôleur + un format de réponse improvisé (HTML ? JSON ? fragment ?)
- ❌ **Boilerplate** considérable à chaque nouveau comportement

### Cas d'usage légitimes (aujourd'hui, et demain)

- **Comportements purement client** : ouvrir un menu, toggler une classe CSS, copier dans le presse-papier
- C'est **exactement le rôle résiduel de Stimulus** dans l'écosystème Symfony UX — il ne disparaît pas, il est recadré.

---

## Slide 1.9 — Concaténation HTML brute en PHP (antipattern historique)

Encore trop vu dans les legacies : on génère des morceaux d'UI directement en PHP.

### Exemple à ne **pas** reproduire

```php
public function renderUserCard(User $user): string
{
    $html  = '<div class="card">';
    $html .= '<h3>' . htmlspecialchars($user->getName()) . '</h3>';
    $html .= '<p>' . htmlspecialchars($user->getEmail()) . '</p>';

    if ($user->isAdmin()) {
        $html .= '<span class="badge">Admin</span>';
    }

    return $html . '</div>';
}
```

### Pourquoi c'est mauvais

- ❌ **Illisible** : le markup se perd dans la syntaxe PHP
- ❌ **XSS garanti** à la moindre négligence
- ❌ **Impossible à themer** : pas de surcharge de template
- ❌ **Pas de séparation** markup/logique
- ❌ **Untestable** visuellement
- ❌ **Diff Git infâme** sur n'importe quelle modif de markup

Mentionné ici **pour clôturer l'inventaire** et pour qu'on sache reconnaître l'antipattern quand on le rencontre dans un vieux projet.

---

## 💬 Message clé (slide conclusion)

> **"On bricolait des composants… sans vrai modèle de composant."**

Chaque approche résout **un morceau** du problème, jamais l'ensemble. On cumule les techniques pour combler les trous — et on finit avec un patchwork difficile à maintenir.

### Tableau récapitulatif

| Approche | Logique PHP | Template séparé | Props typées | État | Perf | Interactivité |
|----------|:-----------:|:---------------:|:------------:|:----:|:----:|:-------------:|
| `include` | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `macro` | ❌ | ✅ | ⚠️ | ❌ | ✅ | ❌ |
| `render(controller())` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Twig extension | ⚠️ | ⚠️ | ❌ | ❌ | ✅ | ❌ |
| Form Type détourné | ✅ | ✅ | ⚠️ | ❌ | ✅ | ❌ |
| Template + Stimulus | ❌ | ✅ | ❌ | ⚠️ client | ✅ | ✅ |
| HTML concaténé en PHP | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |

**Lecture** : aucune ligne ne coche toutes les cases. Le modèle qu'on cherche — **logique PHP + template séparé + props typées + état + interactivité + perf** — n'existe pas encore.

---

## 🧠 Le vrai problème de fond

Au-delà de la grille ci-dessus, **trois douleurs transversales** ressortent de toutes ces approches :

1. **Pas de contrat de typage** entre la vue et ses données d'entrée
   On navigue à l'aveugle : pas d'autocomplétion, pas d'erreur statique, refactor dangereux.

2. **Pas d'unité d'organisation** naturelle
   La logique, le markup et le comportement d'un même bloc d'UI sont disséminés sur 3–4 fichiers dans des dossiers différents.

3. **Pas de réactivité server-driven**
   Pour afficher un comportement qui dépend d'un état serveur (panier, filtre, formulaire complexe), on **sort de Symfony** et on réimplémente côté client.

C'est cette **triple douleur** que Symfony UX va attaquer, en deux temps :

- **Twig Components** (chapitre 3) → unité d'organisation + props typées
- **Live Components** (chapitre 4) → réactivité server-driven sans SPA

---

## 🧭 Transition vers le chapitre 2

Avant de plonger dans les briques, un court détour : **qu'est-ce qu'on emprunte au modèle composant JS moderne**, et **comment Symfony UX le traduit en PHP** ?
