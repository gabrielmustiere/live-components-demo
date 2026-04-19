# Exemple — Chapitre 02 (référence de style)

Ce document montre **comment mapper** une section markdown de `docs/plan/NN-*.md` vers une slide reveal.js Twig. Il reprend le chapitre 02 (déjà implémenté) comme étalon.

---

## 1. Entrée du chapitre — `_chapter.html.twig`

Une slide horizontale qui wrap toutes les slides verticales du chapitre :

```twig
{#
    Chapitre 02 — Avant Symfony UX : les solutions historiques.

    Architecture reveal.js :
    - Ce fichier = UNE slide horizontale (index 2) composée de sous-sections.
    - Chaque sous-fichier = UNE slide verticale (contient donc <section>…</section>).
    - L'ordre des include ci-dessous = l'ordre de navigation avec ↓.

    Pour ajouter / réordonner des slides : ajuster la liste des include.
#}
<section
    data-chapter="02"
    data-chapter-title="Avant Symfony UX"
>
    {% include 'presentation/chapters/02-avant-symfony-ux/slides/_01_intro.html.twig' %}
    {% include 'presentation/chapters/02-avant-symfony-ux/slides/_02_twig_pur.html.twig' %}
    {% include 'presentation/chapters/02-avant-symfony-ux/slides/_03_render_controller.html.twig' %}
    {# ... etc ... #}
    {% include 'presentation/chapters/02-avant-symfony-ux/slides/_09_transition.html.twig' %}
</section>
```

**Convention** :
- Un commentaire Twig d'en-tête explique l'architecture reveal pour quiconque ouvre le fichier.
- Attributs `data-chapter` / `data-chapter-title` servent au debug et à d'éventuelles features transverses.

---

## 2. Slide d'intro — `_01_intro.html.twig`

Mapping : la section « 🎯 Objectif du chapitre » du markdown devient la slide d'annonce.

```twig
{% import 'presentation/chapters/02-avant-symfony-ux/_macros.html.twig' as ui %}

<section data-slide-id="02-intro">
    {{ ui.slide_header('Chapitre 2', 'Avant Symfony UX', 'Les solutions historiques') }}

    <p>
        <strong>Archéologie</strong> des techniques utilisées avant Symfony UX pour
        composer de l'UI côté serveur.
    </p>

    <ul>
        <li>Quelles approches ont été utilisées ?</li>
        <li>Que résolvent-elles, que laissent-elles de côté ?</li>
        <li>Pourquoi aucune, seule, ne suffit ?</li>
    </ul>

    <p><small>↓ pour démarrer l'inventaire</small></p>
</section>
```

**Points à noter** :
- `slide_header('Chapitre 2', …)` au lieu de `'2.X'` : on balise comme slide de titre.
- **3 questions structurantes** plutôt qu'un pavé.
- La micro-indication `↓ pour démarrer` oriente le public vers la bonne touche de navigation.

---

## 3. Slide concept avec code côte-à-côte — `_02_twig_pur.html.twig`

Mapping : « Slide 2.1 — Twig `include` » + « Slide 2.2 — Twig `macro` » du markdown (deux concepts parents → fusionnés en une slide comparative).

```twig
{% import 'presentation/chapters/02-avant-symfony-ux/_macros.html.twig' as ui %}

<section data-slide-id="02-twig-pur">
    {{ ui.slide_header('2.1', 'Twig pur', 'include & macro — factorisation du markup') }}

    <div class="code-split">
        <div class="code-split__col">
            <h3>include</h3>
<pre><code class="language-twig">{% verbatim %}{# _alert.html.twig #}
<div class="alert alert-{{ type }}">
    {{ message }}
</div>

{# Appel (contexte isolé) #}
{% include '_alert.html.twig'
    with { type: 'error', message: 'Oops' } only %}{% endverbatim %}</code></pre>
        </div>

        <div class="code-split__col">
            <h3>macro</h3>
<pre><code class="language-twig">{% verbatim %}{# ui.html.twig #}
{% macro button(label, variant = 'primary') %}
    <button class="btn btn-{{ variant }}">{{ label }}</button>
{% endmacro %}

{# Appel #}
{% import 'ui.html.twig' as ui %}
{{ ui.button('Acheter', 'success') }}{% endverbatim %}</code></pre>
        </div>
    </div>

    {{ ui.pros_cons(
        [
            'Factorisation du <strong>markup répété</strong>',
            'Template séparé, lisible, testable',
        ],
        [
            '<strong>Zéro logique PHP</strong> — tout reste dans Twig',
            'Pas de props typées, pas de validation, pas d\'état',
            '<code>include</code> : fuite de contexte si on oublie <code>only</code> · <code>macro</code> : pas de slots',
        ]
    ) }}
</section>
```

**Points à noter** :
- `<pre>` **collé à la marge gauche** (pas d'indentation) pour que le rendu du code ne soit pas décalé.
- `{% verbatim %}` entoure le code Twig exemple — sinon le parser interprète les `{{ }}`.
- Les pros / cons du markdown deviennent un appel `ui.pros_cons([...], [...])`.
- Le sous-titre du `slide_header` donne le récap instantané de la slide.

---

## 4. Slide anti-pattern — `_06_antipattern.html.twig`

Mapping : une slide qui met en avant du **mauvais code**. On passe par `r-stretch` pour que le bloc occupe la slide.

```twig
{% import 'presentation/chapters/02-avant-symfony-ux/_macros.html.twig' as ui %}

<section data-slide-id="02-antipattern">
    {{ ui.slide_header('2.5', 'Concaténation HTML en PHP', '⚠️ Antipattern historique') }}

<pre class="r-stretch"><code class="language-php">public function renderUserCard(User $user): string
{
    $html  = '&lt;div class="card"&gt;';
    $html .= '&lt;h3&gt;' . htmlspecialchars($user->getName()) . '&lt;/h3&gt;';
    $html .= '&lt;p&gt;'  . htmlspecialchars($user->getEmail()) . '&lt;/p&gt;';

    if ($user->isAdmin()) {
        $html .= '&lt;span class="badge"&gt;Admin&lt;/span&gt;';
    }

    return $html . '&lt;/div&gt;';
}</code></pre>

    <ul>
        <li><strong>Illisible</strong> : markup noyé dans la syntaxe PHP</li>
        <li><strong>XSS garanti</strong> à la moindre négligence</li>
        <li>Impossible à themer, impossible à tester visuellement</li>
        <li>Diff Git infâme sur la moindre modif</li>
    </ul>

    {{ ui.verdict('Mentionné pour l\'inventaire — à reconnaître et éliminer dans les legacies.') }}
</section>
```

**Points à noter** :
- Les chevrons HTML **dans une string PHP** sont échappés (`&lt;`, `&gt;`) sinon le navigateur les interprète.
- `r-stretch` absorbe la hauteur disponible pour le bloc code.
- Le `verdict` donne la prise de position claire en fin de slide.

---

## 5. Slide de récap — `_07_recap.html.twig`

Mapping : un tableau comparatif multi-critères du markdown devient un `<table class="recap-table">` en dark.

```twig
{% import 'presentation/chapters/02-avant-symfony-ux/_macros.html.twig' as ui %}

<section data-slide-id="02-recap">
    {{ ui.slide_header('2.6', 'Tableau récapitulatif', 'Aucune ligne ne coche toutes les cases') }}

    <table class="recap-table">
        <thead>
            <tr>
                <th>Approche</th>
                <th>Logique PHP</th>
                <th>Tpl séparé</th>
                <th>Props typées</th>
                <th>État</th>
                <th>Perf</th>
                <th>Interactif</th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code>include</code></td>          <td>❌</td><td>✅</td><td>❌</td><td>❌</td><td>✅</td><td>❌</td></tr>
            <tr><td><code>macro</code></td>            <td>❌</td><td>✅</td><td>⚠️</td><td>❌</td><td>✅</td><td>❌</td></tr>
            <tr><td><code>render(controller)</code></td><td>✅</td><td>✅</td><td>❌</td><td>❌</td><td>❌</td><td>❌</td></tr>
            <tr><td>Twig extension</td>                <td>⚠️</td><td>⚠️</td><td>❌</td><td>❌</td><td>✅</td><td>❌</td></tr>
            <tr><td>Form Type détourné</td>            <td>✅</td><td>✅</td><td>⚠️</td><td>❌</td><td>✅</td><td>❌</td></tr>
            <tr><td>Template + Stimulus</td>           <td>❌</td><td>✅</td><td>❌</td><td>⚠️</td><td>✅</td><td>✅</td></tr>
            <tr><td>HTML concaténé</td>                <td>✅</td><td>❌</td><td>❌</td><td>❌</td><td>✅</td><td>❌</td></tr>
        </tbody>
    </table>

    {{ ui.verdict('"On bricolait des composants… sans vrai modèle de composant."') }}
</section>
```

**Points à noter** :
- La classe `.recap-table` réduit automatiquement la police à 0.55em pour faire tenir un tableau dense sur la slide.
- Le `verdict` de fin est **une citation** qui condense le message du chapitre — pensé pour être reprise à l'oral.

---

## 6. Slide de transition — `_09_transition.html.twig`

Mapping : la fin du chapitre **amorce** explicitement le chapitre suivant.

```twig
{% import 'presentation/chapters/02-avant-symfony-ux/_macros.html.twig' as ui %}

<section data-slide-id="02-transition">
    {{ ui.slide_header('Transition', 'Cap sur le chapitre 3') }}

    <blockquote>
        Avant de regarder la réponse de Symfony UX, faisons un détour par
        <strong>l'autre côté du mur</strong>.
    </blockquote>

    <ul>
        <li>Pourquoi le modèle composant a <strong>gagné côté JavaScript</strong> ?</li>
        <li>Quels concepts précis va-t-on ramener côté serveur ?</li>
    </ul>

    <p><small>→ pour le chapitre 3</small></p>
</section>
```

**Points à noter** :
- `slide_header('Transition', …)` — on signale au public qu'on ferme une boucle.
- Questions qui **déclenchent la curiosité** pour le chapitre suivant (pas de réponses données ici).
- `→ pour le chapitre N+1` oriente la navigation.

---

## 7. Macros chapitre-locales — `_macros.html.twig`

Le chapitre 02 a besoin de patterns récurrents non couverts par `presentation/_macros.html.twig`. Il les factorise localement :

```twig
{# Macros partagees du chapitre 02. #}

{# Bandeau d'identification (numero + titre court) #}
{% macro slide_header(number, title, subtitle = null, fit_title = false) %}
    <header class="slide-header">
        <span class="slide-header__number">{{ number }}</span>
        <h2 class="slide-header__title{% if fit_title %} r-fit-text{% endif %}">{{ title }}</h2>
        {% if subtitle %}<p class="slide-header__subtitle">{{ subtitle }}</p>{% endif %}
    </header>
{% endmacro %}

{# Carte "Ce que ca resout / Ce que ca ne resout pas" #}
{% macro pros_cons(pros = [], cons = []) %}
    <div class="pros-cons">
        {% if pros|length > 0 %}
            <div class="pros-cons__col pros-cons__col--pros">
                <h3>✅ Ce que ça résout</h3>
                <ul>
                    {% for item in pros %}<li>{{ item|raw }}</li>{% endfor %}
                </ul>
            </div>
        {% endif %}
        {% if cons|length > 0 %}
            <div class="pros-cons__col pros-cons__col--cons">
                <h3>❌ Ce que ça ne résout pas</h3>
                <ul>
                    {% for item in cons %}<li>{{ item|raw }}</li>{% endfor %}
                </ul>
            </div>
        {% endif %}
    </div>
{% endmacro %}

{# Verdict final #}
{% macro verdict(text) %}
    <blockquote class="verdict">{{ text|raw }}</blockquote>
{% endmacro %}
```

**Note** : `slide_header` est **dupliqué** entre `presentation/_macros.html.twig` et le chapitre 02 — c'est un artefact historique. Pour un nouveau chapitre, **importer la version transverse** (`{% import 'presentation/_macros.html.twig' as ui %}`) sauf si on a besoin de `pros_cons` / `verdict` (alors créer un `_macros.html.twig` local et y copier ces deux-là).

---

## Checklist de mapping markdown → slides

Pour chaque chapitre du plan, se poser ces questions dans l'ordre :

1. **Combien de slides** ? (≈ une slide par "Slide N.X" du markdown, plus intro et transition)
2. **Quels patterns** reviennent ? (pros/cons, verdict, recap-table, code-split) → macros locales ou transverses ?
3. **Quels langages de code** ? (PHP, Twig, JS, bash) → coloration syntaxique correcte.
4. **Y a-t-il un tableau comparatif** en fin de chapitre ? → `.recap-table`.
5. **Y a-t-il une démo interactive** attendue ? → Live Component séparé (cf. `references/demo-live-component.md`).
6. **Transition** cohérente avec l'intro du chapitre N+1 ? → lire N+1 avant d'écrire la slide de transition.
