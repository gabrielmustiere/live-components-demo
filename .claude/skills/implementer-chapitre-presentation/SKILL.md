---
name: implementer-chapitre-presentation
description: Implémente un chapitre du plan (`docs/plan/NN-*.md`) sous forme de slides reveal.js dans `templates/presentation/chapters/NN-slug/`, avec si besoin une démo de code (Live Component) fonctionnelle. À déclencher dès que l'utilisateur demande d'implémenter, créer, générer, monter, transformer, matérialiser ou slidifier un chapitre du plan en slides — même sans le mot "reveal" ("monte les slides du chapitre 5", "implémente le chapitre 7", "transforme docs/plan/08 en reveal", "génère les slides du chapitre cas d'usage", "fais la démo du chapitre 11"). Utilise le chapitre 02 (`templates/presentation/chapters/02-avant-symfony-ux/`) comme référence de style. Peut créer un Live Component (`src/Twig/Components/`) + template + page démo quand le chapitre a besoin d'une démonstration interactive.
---

# Implémenter un chapitre de la présentation en reveal.js

## Contexte

Ce projet est une présentation Symfony UX / Live Components. Le plan narratif vit dans `docs/plan/NN-*.md` (markdown dense, pédagogique). La présentation elle-même est une **reveal.js embarquée** dans une page Symfony, avec un modèle de slides Twig :

```
templates/presentation/
├── _cover.html.twig                 # slide 0 de couverture
├── _macros.html.twig                # slide_header partagé à toute la prés.
├── index.html.twig                  # déclare l'ordre des chapitres
├── layout.html.twig                 # wrap layout Symfony
└── chapters/
    └── NN-slug/
        ├── _chapter.html.twig       # UNE slide horizontale (le chapitre)
        ├── _macros.html.twig        # macros spécifiques au chapitre (optionnel)
        └── slides/
            ├── _01_*.html.twig      # slides verticales (↓ dans reveal)
            └── _02_*.html.twig
```

**Seul le chapitre 02 est entièrement implémenté.** Il sert d'étalon de style et de référence d'architecture. Les autres chapitres n'exposent souvent qu'une slide d'annonce.

La démo technique vit à part (ex. `src/Twig/Components/Counter.php` + `templates/components/Counter.html.twig` + route `/demo/counter`). La présentation peut **pointer vers une démo** via `.btn-primary` avec lien, ou intégrer un composant Live directement dans une slide si pertinent (chapitre démo live typiquement).

## Quand utiliser ce skill

- L'utilisateur demande d'**implémenter / générer / monter / matérialiser / slidifier** un chapitre du plan.
- L'utilisateur pointe un `docs/plan/NN-*.md` et évoque la mise en slides reveal.
- L'utilisateur demande de **créer la démo** d'un chapitre (code + page).

Ne pas déclencher si l'utilisateur demande d'**enrichir le markdown** du plan (→ c'est le skill `enrichir-chapitre-plan`) ou de modifier la structure reveal globale.

## Étapes

### 1. Cartographier l'existant

Lire **dans cet ordre**, avant d'écrire une ligne :

1. `docs/plan/NN-*.md` — le chapitre cible (source narrative).
2. `templates/presentation/chapters/NN-slug/` — ce qui existe déjà (souvent juste `_chapter.html.twig` avec la slide titre « À venir »).
3. `templates/presentation/chapters/02-avant-symfony-ux/` — modèle de référence (structure, macros, naming).
4. `templates/presentation/_macros.html.twig` — macros transverses disponibles (`slide_header`).
5. `assets/styles/presentation.css` — pour **connaître les classes CSS déjà disponibles** (voir § Inventaire CSS plus bas) et éviter d'inventer un style qui existe.
6. `templates/presentation/index.html.twig` — vérifier que le chapitre est bien inclus (il devrait déjà l'être).

Identifier :
- Les **slides à produire** (en général une slide verticale par « Slide N.X » du markdown, plus intro et transition).
- Les **patterns visuels récurrents** du chapitre → candidats à un `_macros.html.twig` local.
- Les **blocs de code** à rendre (PHP / Twig / JS / shell) → langages de coloration syntaxique.
- Si une **démo interactive** est attendue (chapitre démo live, ou composant démontré).

### 2. Proposer un plan avant d'écrire

Sauf enrichissement trivial, annoncer à l'utilisateur :

- Liste des slides verticales prévues (ordre, titre court, idée centrale).
- Macros locales envisagées (seulement si un pattern se répète 3+ fois dans le chapitre).
- Besoin ou pas d'une démo de code ; si oui : composant + route + URL cible.
- Vérifications doc Symfony (WebFetch) si des APIs Live / Twig Components sont évoquées.

L'utilisateur peut rectifier avant qu'on génère une dizaine de fichiers.

### 3. Architecture d'un chapitre

Le fichier `_chapter.html.twig` est **une seule slide horizontale** qui **contient** les sous-sections verticales (reveal.js navigation : `←/→` entre chapitres, `↓/↑` à l'intérieur).

```twig
{# _chapter.html.twig #}
<section
    data-chapter="NN"
    data-chapter-title="Titre court du chapitre"
>
    {% include 'presentation/chapters/NN-slug/slides/_01_intro.html.twig' %}
    {% include 'presentation/chapters/NN-slug/slides/_02_xxx.html.twig' %}
    {# ... #}
    {% include 'presentation/chapters/NN-slug/slides/_NN_transition.html.twig' %}
</section>
```

Chaque fichier `slides/_XX_*.html.twig` contient **exactement un `<section>`** (la slide verticale).

```twig
{% import 'presentation/chapters/NN-slug/_macros.html.twig' as ui %}
{# ou si pas de macros locales : #}
{% import 'presentation/_macros.html.twig' as ui %}

<section data-slide-id="NN-slug-slide">
    {{ ui.slide_header('N.X', 'Titre court', 'Sous-titre optionnel') }}

    {# contenu de la slide #}
</section>
```

**Conventions de naming** :
- Dossier chapitre : `NN-slug-descriptif/` (miroir du nom du fichier markdown).
- Fichier slide : `_XX_nom_court.html.twig` (numéroté pour l'ordre).
- `data-slide-id` : `NN-slug` unique, sert pour l'ancre URL et le debug.

### 4. Patterns éditoriaux (par type de slide)

| Rôle de slide | Ingrédients utiles |
|---------------|--------------------|
| **Intro de chapitre** (`_01_intro`) | `slide_header('Chapitre N', …)`, 2–3 questions structurantes, `↓ pour démarrer` |
| **Concept / technique** | `slide_header('N.X', …)`, exemple code + `pros_cons`, `verdict` |
| **Comparaison** | Tableau `.recap-table` OU `.code-split` (2 blocs de code côte à côte) |
| **Anti-pattern** | `<pre class="r-stretch"><code>...</code></pre>` + liste puce des douleurs + `verdict` |
| **Récap / synthèse** | `.recap-table` multi-critères + citation `verdict` |
| **Respiration / fond** | Visuel pleine slide, citation, peu de texte |
| **Transition** (`_NN_transition`) | Hook vers le chapitre suivant, `→ pour le chapitre N+1` |

Ne pas plaquer le moule si le chapitre est d'un autre type (démo live, conclusion…). Composer à partir de la source markdown.

### 5. Inventaire des classes CSS et helpers disponibles

Avant d'ajouter du CSS, vérifier si le besoin est déjà couvert. **Ne pas toucher `presentation.css` sauf si un pattern nouveau est vraiment récurrent.**

**Macros transverses** (`presentation/_macros.html.twig`) :
- `slide_header(number, title, subtitle = null, fit_title = false)` — en-tête standard.

**Macros utiles à recopier depuis le chapitre 02** (si le chapitre partage ces patterns, créer un `_macros.html.twig` local) :
- `pros_cons(pros, cons)` — deux colonnes « ✅ Ce que ça résout / ❌ Ce que ça ne résout pas ».
- `verdict(text)` — encart ambre de conclusion.
- `legitimate_uses(items)` — encart vert « 🎯 Cas d'usage légitimes ».

**Classes CSS** (déjà stylées dans `presentation.css`) :
- `.slide-header`, `.slide-header__number`, `.slide-header__title`, `.slide-header__subtitle`
- `.pros-cons`, `.pros-cons__col`, `.pros-cons__col--pros`, `.pros-cons__col--cons`
- `.verdict`, `.legitimate`
- `.recap-table` (tableaux multi-critères, polices réduites)
- `.code-split`, `.code-split__col` (blocs de code empilés)
- `.btn-primary` (CTA vers une démo)

**Helpers reveal.js natifs** (classes spéciales reveal) :
- `r-fit-text` — auto-scale d'un titre sur une slide.
- `r-stretch` — étire un élément (ex. `<pre>`) pour remplir la slide verticalement.
- `fragment` (sur un enfant) — apparition progressive au clic (`<li class="fragment">`).

### 6. Écrire les blocs de code dans les slides

Les blocs de code sont rendus par **reveal-highlight** (plugin déjà chargé). Conventions :

**Code Twig** (protège les `{{ }}` contre l'interprétation Twig du template lui-même) :

```twig
<pre><code class="language-twig">{% verbatim %}{# templates/_alert.html.twig #}
<div class="alert alert-{{ type }}">
    {{ message }}
</div>{% endverbatim %}</code></pre>
```

**Code PHP** (échapper les chevrons HTML si du markup est embarqué dans une string) :

```twig
<pre><code class="language-php">public function render(): string
{
    return '&lt;div class="card"&gt;...&lt;/div&gt;';
}</code></pre>
```

**Code JavaScript / shell** : `class="language-javascript"` / `class="language-bash"`, pas de `verbatim` nécessaire.

**Pour stretcher un gros bloc** : `<pre class="r-stretch"><code>…</code></pre>`.

**Deux blocs côte à côte** : utiliser le pattern `.code-split` (cf. chapitre 02 slide `_02_twig_pur`).

**Important** — les `<pre>` ne doivent **pas** être indentés dans le Twig (l'indentation se retrouve dans le rendu, même avec `<code>`). Coller `<pre>` à la marge gauche, même si ça casse l'indentation du fichier.

### 7. Macros locales au chapitre (optionnel)

Créer un `templates/presentation/chapters/NN-slug/_macros.html.twig` uniquement si :
- **3+ slides** du chapitre partagent exactement le même pattern visuel non couvert par les macros transverses, OU
- Le chapitre a un pattern très spécifique (ex. diagramme de cycle de vie, tableau de features Live Components).

Sinon, consommer `presentation/_macros.html.twig` et importer depuis chaque slide.

Si plusieurs chapitres ont besoin du **même** pattern (ex. `pros_cons` du chapitre 02), **proposer à l'utilisateur de le promouvoir** dans `presentation/_macros.html.twig` plutôt que de dupliquer.

### 8. Démo de code (si le chapitre l'exige)

Un chapitre « démo », « cas d'usage », « live components » peut demander un composant Live fonctionnel. Pattern de référence : `src/Twig/Components/Counter.php`.

**Fichiers à produire** :

```
src/Twig/Components/Xxx.php              # #[AsLiveComponent] + LiveProps + LiveActions
templates/components/Xxx.html.twig        # markup + data-action="live#action"
templates/demo/xxx.html.twig              # page démo standalone
src/Controller/DemoController.php         # ajouter une route /demo/xxx
```

**Contraintes projet (cf. CLAUDE.md)** :
- `declare(strict_types=1)` en tête de chaque `.php`.
- Final class, attributs PHP 8 (`#[AsLiveComponent]`, `#[LiveProp]`, `#[LiveAction]`).
- `use DefaultActionTrait;` dans le composant.
- Template Twig du composant utilise `{{ attributes.defaults({ class: '...' }) }}` pour autoriser le parent à ajouter des classes.
- Boutons : `data-action="live#action"` + `data-live-action-param="nomDeLAction"`.
- Tests visuels via `symfony serve` puis `/demo/xxx`.

**Dans les slides** : soit incruster `<twig:Xxx />` directement dans une slide (démo in-place), soit pointer vers `/demo/xxx` via `<a class="btn-primary" href="{{ path('app_demo_xxx') }}">Ouvrir la démo</a>`.

**Toujours vérifier les noms d'attributs Symfony UX** via WebFetch sur la doc officielle si on n'est pas certain (`LiveProp` modifiers, `LiveAction`, événements) :
- https://symfony.com/bundles/ux-live-component/current/index.html
- https://symfony.com/bundles/ux-twig-component/current/index.html

### 9. Cohérence narrative

- Reprendre le **vocabulaire du markdown source** : si le plan parle de « trois douleurs » ou « server-driven », les slides utilisent les mêmes termes.
- **Callbacks** explicites vers les chapitres précédents (« comme vu au chapitre N… »), surtout pour un chapitre central.
- La slide de **transition** doit amorcer le chapitre suivant en une phrase — aller lire l'intro du chapitre N+1 pour caler le hook.

### 10. Style et voix

- **Langue** : français, tous les accents et diacritiques préservés (jamais « ecosysteme », « decoupe »).
- **Ton slide** : plus bref que le markdown source. Sur slide, **1 idée = 1 slide**. Couper les tartines : le script oral (narration) reste dans le markdown, pas dans la slide.
- **Emojis** : modérés, essentiellement dans les titres de section ou les verdicts (`✅`, `❌`, `⚠️`, `🎯`, `💬`).
- **Code dans les slides** : exemples minimaux, parfois raccourcis par rapport au markdown. Préférer la lisibilité à l'exhaustivité (le public doit pouvoir lire en 5 secondes).
- **Pas de commentaires superflus** dans les fichiers Twig autres que l'en-tête explicatif d'un `_chapter.html.twig` complexe.

### 11. Après écriture

Résumer en 4–6 bullets :
- Fichiers créés / modifiés (avec `file_path:line_number` si utile).
- Slides produites (liste courte).
- Démo ajoutée ou non, URL si oui.
- Ajouts éventuels au CSS (à éviter en général — signaler).
- Point d'attention (ex. « j'ai inventé le nom `onChanged` pour un LiveProp, à vérifier sur la doc »).
- Commandes utiles : `symfony serve` puis `/presentation#/NN`.

Si un chapitre produit plus de ~6 slides et 200 lignes, proposer à l'utilisateur de valider la première moitié avant d'aller plus loin.

## Anti-patterns à éviter

- ❌ Mettre **plusieurs `<section>`** dans un même fichier `slides/_XX_*.html.twig` (casse la navigation reveal).
- ❌ Indenter les `<pre>` dans le Twig (l'indentation apparaît dans le rendu).
- ❌ Oublier `{% verbatim %}` autour d'un code Twig → le parser interprète les `{{ }}` comme du template.
- ❌ Recréer du CSS pour un pattern déjà stylé (`.pros-cons`, `.verdict`, etc.).
- ❌ Dupliquer une macro chapitre-spécifique au lieu de la promouvoir dans `_macros.html.twig` transverse quand elle est réutilisée.
- ❌ Produire une slide-tartine : si la slide a 10 bullets et 40 mots par bullet, elle est à scinder.
- ❌ Inventer des attributs Live Components de mémoire au lieu de vérifier la doc.
- ❌ Mettre la démo directement dans le `_chapter.html.twig` sans passer par un composant Live réel (le public doit voir du vrai code tourner).
- ❌ Oublier de mettre à jour `templates/presentation/index.html.twig` si un chapitre nouveau est ajouté (normalement déjà inclus pour les 13 chapitres du plan actuel).

## Références bundlées

- `references/exemple-chapitre-02.md` : extraits commentés du chapitre 02 (intro, slide concept, anti-pattern, récap, transition) — montre comment mapper une section markdown vers une slide Twig.
- `references/demo-live-component.md` : squelette minimal d'un Live Component (PHP + Twig + page démo + route) calé sur les conventions du projet.
