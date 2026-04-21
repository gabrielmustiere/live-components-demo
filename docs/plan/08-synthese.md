# 🧩 9. Synthèse — Comparaison, limites & take-aways

## 🎯 Objectif du chapitre

Refermer la boucle. Trois slides, denses :

1. **Le tableau de synthèse** : Twig / Live / React — quand choisir quoi, axe par axe
2. **Les limites honnêtes** de Live Components + les anti-patterns à éviter
3. **Les take-aways actionnables** — ce qu'on fait dès lundi

---

## Slide 9.1 — Tableau comparatif (Twig / Live / React-Vue)

### La grille élargie (9 axes)

| Feature                     | Twig Component                | Live Component                     | React / Vue                        |
|-----------------------------|-------------------------------|------------------------------------|------------------------------------|
| **Lieu du rendu**           | Serveur uniquement            | Serveur + patch Ajax (morphdom)    | Client (SSR optionnel)             |
| **Lieu de l'état**          | Pas d'état (rendu figé)       | Serveur, sérialisé dans le DOM     | Client, en mémoire JS              |
| **JS requis**               | ❌ Aucun                       | ⚠️ Stimulus inclus (0 code à écrire) | ✅ Oui, significatif                |
| **Pipeline de build**       | ❌ (AssetMapper suffit)        | ❌ (AssetMapper suffit)             | ✅ Vite / Webpack / esbuild         |
| **Poids JS côté client**    | ~0 Ko                         | ~25–30 Ko (Stimulus + live)        | 40–150 Ko+ selon la stack          |
| **SEO natif**               | ✅ HTML complet au 1er paint   | ✅ HTML complet au 1er paint        | ⚠️ SSR ou prerender requis         |
| **Fonctionnement offline**  | ❌                             | ❌                                  | ✅ (service workers, PWA)           |
| **Latence interaction**     | N/A                           | Réseau (50–300 ms typique)         | Locale (instantanée)               |
| **Duplication PHP/JS**      | Aucune                        | Aucune                             | Fréquente (validation, formatage)  |
| **Courbe d'apprentissage**  | Faible (Twig enrichi)         | Moyenne (quelques conventions)     | Élevée (écosystème entier)         |
| **Testabilité**             | PHPUnit + rendu                | PHPUnit + `InteractsWithLiveComponents` | Jest/Vitest + testing-library   |
| **Accessibilité**           | Natif (HTML pur)              | Natif (morphing préserve focus)     | Dépend de la discipline équipe     |

### Où brille chaque approche

- **Twig Components** → design system, primitives UI, blocks de layout, cards read-only, dashboards statiques
- **Live Components** → filtres/pagination, search-as-you-type, formulaires dynamiques, wizards, quick edits, compteurs persistés
- **React / Vue** → canvas, drag & drop intensif, éditeurs WYSIWYG riches, offline, animations 60 fps, apps collaboratives temps réel

### La règle des deux questions

```
1. Y a-t-il une interaction utilisateur qui modifie l'UI ?
   └─ Non → Twig Component
   └─ Oui  → Question 2

2. Cette interaction dépend-elle d'un état ou de données côté serveur ?
   └─ Non → Stimulus (comportement purement client)
   └─ Oui → Live Component
```

**Exception** : dès que latence < 100 ms est **critique pour l'UX** (canvas, drag fluide, 60 fps) → basculer en client JS.

### L'hybridation, le vrai modèle

Le meilleur projet Symfony moderne n'est **pas 100 % Twig**, ni **100 % Live**, ni **100 % React**. C'est une app qui **place la bonne brique au bon endroit**. Exemple d'un backoffice e-commerce :

| Zone                            | Brique choisie        |
|---------------------------------|-----------------------|
| Layout, header, sidebar, footer | Twig Component        |
| Design system (buttons, cards)  | Twig Component        |
| Liste produits filtrable        | Live Component        |
| Formulaire commande (wizard)    | Live Component        |
| Éditeur de fiche produit riche  | React (zone isolée)   |
| Charts dashboard                | Twig + Chart.js       |

**Une seule stack** (Symfony + Twig), **deux moteurs de réactivité** (Live et React-île), **un design system** unifié.

---

## Slide 9.2 — Limites & anti-patterns

### Les limites honnêtes de Live Components

#### Latence serveur

| Environnement | Latence | Perception |
|---------------|---------|------------|
| Dev local | 30–80 ms | Imperceptible |
| Prod, même région | 80–200 ms | Acceptable |
| Prod, mobile 4G | 200–500 ms | Visible mais OK |
| Prod, 3G / réseau dégradé | 500 ms–2 s | Frustrant |

**Où ça fait mal** : interactions très fréquentes (typing sans debounce), UI 60 fps, utilisateurs en mobilité.

#### Charge serveur

Un utilisateur qui tape avec debounce = 2–5 req/s. **Chaque** requête rejoue firewall, voters, bootstrap Doctrine, rendu Twig.

**Ordre de grandeur** : une app qui prend 100 req/s en rendu classique → prévoir **300–500 req/s** avec Live Components actifs.

#### Limites structurelles

- ❌ **Pas d'état client riche** : undo/redo local, draft offline → impossibles sans JS custom
- ❌ **Optimistic UI** : possible mais demande du code côté client
- ❌ **Offline** : impossible par nature
- ❌ **Animations complexes** : pas d'équivalent natif à Framer Motion, GSAP

### Les 5 anti-patterns à reconnaître en code review

#### 1. Trop d'état dans une `LiveProp`

```php
// ❌ Entité Doctrine complète sérialisée dans le DOM
#[LiveProp(writable: true)]
public Order $order;

// ✅ ID + rechargement
#[LiveProp]
public int $orderId;

public function getOrder(): Order
{
    return $this->orders->find($this->orderId);
}
```

#### 2. Chaînes de `LiveAction` qui multiplient les round-trips

```twig
{# ❌ 3 requêtes pour une action utilisateur #}
<button data-live-action-param="addItem">Ajouter</button>
<button data-live-action-param="recalcTotal">Total</button>
<button data-live-action-param="saveCart">Sauver</button>

{# ✅ Une seule LiveAction qui fait les 3 étapes #}
<button data-live-action-param="checkout">Valider</button>
```

#### 3. Live Component pour du purement statique

Composant **sans** `LiveProp writable` **ni** `LiveAction` → c'est un **Twig Component**. Tu payes le coût de l'hydratation pour rien.

#### 4. Forcer Live là où la latence tue l'UX

À **éviter** en Live : slider couleur avec preview, éditeur WYSIWYG, canvas de dessin, carte Leaflet avec milliers de markers. → Stimulus pur ou lib client.

#### 5. Ignorer le profiling

Sur une page avec 5 Live Components imbriqués, chaque interaction peut déclencher plusieurs requêtes en cascade. Sans profiler ouvert, tu ne le vois pas (cf. chapitre 8).

### Grille de décision rapide

| Question | Oui → | Non → |
|----------|-------|-------|
| Interaction dépend d'état serveur ? | ✅ Live | Twig ou Stimulus |
| < 1 interaction / seconde suffit ? | ✅ Live | Stimulus client-only |
| Latence ~200 ms tolérable ? | ✅ Live | SPA / client natif |
| Feature utilisée offline ? | SPA / PWA | ✅ Live |
| Animation 60 fps requise ? | Client JS / canvas | ✅ Live |
| Contenu indexé par Google ? | ✅ Live | SPA avec SSR |

**Règle d'or** : si les réponses pointent majoritairement vers Live, c'est un bon fit. Dès qu'une réponse critique bascule → **autre brique**.

---

## Slide 9.3 — Take-aways & plan d'action

### Les 5 idées à emporter

1. **Twig Components** = une classe PHP + un template = un vrai composant serveur, avec props typées, services injectables, colocation
2. **Live Components** = Twig Component + réactivité Ajax = *server-driven UI*, zéro JS à écrire, DOM morphing automatique
3. **Server-driven UI** couvre 80 % des besoins web business (CRUD, backoffice, SaaS B2B, e-commerce interne)
4. **React reste pertinent** pour les cas extrêmes (canvas, offline, 60 fps, éditeurs riches) — pas comme choix par défaut
5. **L'adoption est progressive**, composant par composant, sans big bang, sans nouvelle stack à apprendre

### Ce qu'on fait **dès cette semaine**

- Installer `symfony/ux-twig-component` et `symfony/ux-live-component` sur un projet pilote
- Extraire **un** composant répété (alert, card, badge) en Twig Component
- Écrire **un** Live Component trivial (compteur, toggle, like) pour sentir le cycle
- Ouvrir le profiler, inspecter un `POST /_components/*`, lire le payload

### Ce qu'on fait **ce mois-ci**

- Identifier **un widget JS/Stimulus bricolé** → le réécrire en Live Component, supprimer le JS correspondant
- Commencer une arborescence `src/Twig/Components/` + `templates/components/` (atomic design)
- Mettre les premiers tests `InteractsWithLiveComponents` sur les composants critiques

### Ce qu'on fait **ce trimestre**

- Documenter les règles d'équipe : quand Twig, quand Live, quand Stimulus, quand React
- Mettre en place le playground `ux-twig-component` en dev
- Auditer les pages existantes : combien de `render(controller())`, combien de JS custom ? Construire un backlog de refacto ciblé

### Indicateurs à suivre

- **Lignes de JS custom** dans le projet (doit tendre vers 0 hors cas justifiés)
- **Nombre de composants Twig réutilisés** (mesure d'adoption)
- **Temps de livraison** d'une feature CRUD (avant/après)
- **Nombre de queries Doctrine par Live cycle** sur les pages clé (cf. chapitre 8)

### Pour aller plus loin

- [ux.symfony.com](https://ux.symfony.com) — le hub Symfony UX (tous les bundles)
- [ux.symfony.com/live-component](https://ux.symfony.com/live-component) — Live Components
- [ux.symfony.com/twig-component](https://ux.symfony.com/twig-component) — Twig Components
- [symfony/ux sur GitHub](https://github.com/symfony/ux) — le monorepo, les PRs, les issues
- **Cousins à connaître** : [Livewire](https://livewire.laravel.com/), [Phoenix LiveView](https://hexdocs.pm/phoenix_live_view/), [Hotwire](https://hotwired.dev/), [htmx](https://htmx.org/)

---

## 💬 Mot de la fin

> **"Le meilleur framework front, c'est celui dont on n'a pas besoin."**
>
> Symfony UX ne supprime pas le besoin d'interactivité — il supprime le **besoin d'une stack séparée** pour la gérer. Une équipe, une stack, un design system. Et dans 80 % des cas réels, ça suffit largement.

---

## 🗣️ Narration finale

> "Ce qu'il faut retenir : un Twig Component, c'est une classe et un template. Un Live Component, c'est ce même couple auquel on ajoute `#[LiveProp]` et `#[LiveAction]`, plus un `data-model` côté Twig — et on obtient de la réactivité sans écrire de JS, sans écrire d'endpoint, sans format d'API à inventer.
>
> Pour 80 % des apps Symfony — CRUD, backoffice, e-commerce, SaaS B2B — c'est exactement ce dont on a besoin. Pour les 20 % restants — canvas, offline, 60 fps, éditeurs collaboratifs — React reste la bonne réponse, mais sur une zone isolée, pas sur toute l'app.
>
> Le travail de lundi matin : prendre un widget bricolé en Stimulus+Ajax chez vous, le réécrire en Live Component, supprimer le JS correspondant. En une demi-journée, vous avez votre premier retour d'expérience concret, et vous pouvez juger par vous-mêmes."

---

## 🎤 Questions ouvertes pour la discussion

- Sur vos projets, quelles zones UI mériteraient d'être converties en Live Components ?
- Où placez-vous aujourd'hui la frontière React / Live dans votre produit ?
- Quels sont les widgets JS custom qui traînent depuis le plus longtemps dans votre codebase ?
- Qu'est-ce qui freine l'adoption chez vous — tech, équipe, convictions ?

---

**Merci !**
