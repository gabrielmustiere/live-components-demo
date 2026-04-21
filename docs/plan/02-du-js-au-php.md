# 🔀 2. Du JS au PHP — ce qu'on emprunte, ce qu'on laisse

## 🎯 Objectif du chapitre

Chapitre **court et volontairement dense** — on ne refait pas l'histoire des SPA. On identifie **les concepts précis** du modèle composant JS qu'on veut porter en PHP, puis on regarde **comment Symfony UX les traduit** en idiomes PHP/Symfony.

Deux questions, une slide chacune :

1. **Qu'est-ce qu'on garde** du modèle React/Vue, et **qu'est-ce qu'on laisse** ?
2. **Comment Symfony UX** assemble les briques pour répondre aux 3 douleurs du chapitre 1 ?

---

## Slide 2.1 — Le modèle composant, sans le tooling

### Le postulat universel des frameworks modernes

```
UI = f(state)
```

La vue est une **fonction pure de l'état**. Quand l'état change, la vue se recalcule. Pas de synchronisation manuelle, pas de DOM mutable partagé.

### Cinq concepts transverses (indépendants du langage)

| Concept | Définition | Propriété essentielle |
|---------|-----------|----------------------|
| **Props** | Données passées par le parent | Immuables côté composant, contrat d'entrée explicite |
| **State** | État interne au composant | Mutable localement, déclenche un re-render |
| **Render** | Fonction `(props, state) → UI` | Pure : mêmes entrées = même sortie |
| **Events** | Interactions remontées vers le parent | Couplage minimal |
| **Composition** | Arbre de composants | Décomposition en unités compréhensibles |

### Résonance avec les 3 douleurs du chapitre 1

| Douleur Symfony historique | Réponse du modèle composant |
|----------------------------|-----------------------------|
| Pas de contrat de typage entre vue et données | **Props explicites et typées** |
| Pas d'unité d'organisation (logique/markup dispersés) | **Un composant = une unité colocalisée** |
| Pas de réactivité server-driven | **State + render** : la vue est une projection de l'état |

### Ce qu'on garde / ce qu'on laisse

| Ce qu'on **veut récupérer** | Ce qu'on **laisse côté JS** |
|-----------------------------|-----------------------------|
| Props typées et explicites | Virtual DOM / reconciliation |
| Unité logique = classe + template | Bundle JS de plusieurs MB |
| State comme source de vérité | Hydration côté client |
| Composition (arbre de composants) | Build tools (webpack, Vite…) |
| Réactivité (état → vue automatique) | Double codebase PHP + JS |
| Raisonnement local sur un composant | Store global (Redux, Pinia…) |

### 💬 Message clé

> **"Le modèle composant est une idée. La SPA est une implémentation. Les deux ne sont pas liés."**
>
> On veut la **composition, les props typées, la réactivité**. Pas forcément tout l'écosystème JS qui va avec.

---

## Slide 2.2 — Symfony UX : la traduction en PHP

### Postulat fondateur

> **"Ramener le modèle composant côté serveur, sans renier PHP ni Twig."**

Pas un framework. Une **initiative** qui regroupe une collection de bundles autour d'un socle commun.

### Vue d'ensemble en un schéma

```
┌──────────────────────────────────────────────────────────────┐
│                       Symfony UX                             │
│                                                              │
│   Couche composant PHP                                       │
│   ┌──────────────────────┐    ┌──────────────────────┐       │
│   │  Twig Components     │───▶│  Live Components     │       │
│   │  (statique, props)   │    │  (réactif, état)     │       │
│   └──────────────────────┘    └──────────────────────┘       │
│              │                          │                    │
│              └──────────┬───────────────┘                    │
│                         ▼                                    │
│              Stimulus bridge (autoload JS)                   │
│   ┌──────────────────────────────────────────────────┐       │
│   │  Stimulus  ── controllers JS minimaux            │       │
│   │  Turbo     ── navigation sans rechargement       │       │
│   └──────────────────────────────────────────────────┘       │
│                         │                                    │
│                         ▼                                    │
│   ┌──────────────────────────────────────────────────┐       │
│   │  AssetMapper (importmap, no Node required)       │       │
│   │  ou Webpack Encore (legacy/avancé)               │       │
│   └──────────────────────────────────────────────────┘       │
│                                                              │
│   + bundles satellites : Chartjs, Dropzone, Cropperjs,       │
│     Notify, Autocomplete, LeafletMap, Icons, Toggle…         │
└──────────────────────────────────────────────────────────────┘
```

### Le Stimulus bridge, en 30 secondes

C'est le **mécanisme central** qui rend Symfony UX agréable : un bundle PHP **livre** des contrôleurs Stimulus, qui s'enregistrent **automatiquement** côté client.

```
1. composer require symfony/ux-live-component
        │
        ▼
2. Le bundle expose un controller JS (live_controller.js)
        │
        ▼
3. Symfony Flex écrit dans assets/controllers.json
        │
        ▼
4. AssetMapper / Webpack Encore lit le fichier
        │
        ▼
5. data-controller="live" est dispo partout, zéro npm
```

### Les 3 douleurs du chapitre 1, cochées

| Douleur | Brique de réponse |
|---------|-------------------|
| Contrat de typage | **Twig Components** → props PHP typées, `#[ExposeInTemplate]` |
| Unité d'organisation | **Twig Components** → 1 classe + 1 template colocalisés |
| Réactivité server-driven | **Live Components** → `#[LiveProp]`, `#[LiveAction]`, DOM morphing |

### Grille d'évaluation appliquée

| Brique | Logique PHP | Template séparé | Props typées | État | Interactivité |
|--------|:-----------:|:---------------:|:------------:|:----:|:-------------:|
| **Twig Components** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Live Components** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Stimulus** | ❌ | ⚠️ | ⚠️ | ⚠️ client | ✅ |
| **Turbo** | n/a | n/a | n/a | n/a | ✅ nav |

C'est la **combinaison** qui couvre tout le tableau du chapitre 1.

### 💬 Message clé

> **"Symfony UX = bridge backend ↔ frontend."**
>
> On garde la productivité Symfony, on gagne le modèle composant, on évite la complexité SPA quand elle n'est pas nécessaire.

---

## 🗣️ Narration (script oral)

> "Le modèle composant, c'est cinq idées : props, state, render, events, composition. Indépendantes du langage. React et Vue les implémentent en JS — mais rien, conceptuellement, n'empêche de les porter en PHP.
>
> Symfony UX fait exactement ça. Twig Components apporte les props typées et l'unité d'organisation. Live Components ajoute l'état et la réactivité, via un round-trip Ajax et du DOM morphing. Stimulus reste là pour les cas purement client. Turbo gère la navigation. Et tout ça communique par un pont unique — le Stimulus bridge — qui permet à un `composer require` de livrer du JS sans que tu touches à npm."

---

## 🧭 Transition vers le chapitre 3

La vision est posée. Attaquons la **première brique** : **Twig Components**, le socle qui apporte classe PHP + template + props typées.
