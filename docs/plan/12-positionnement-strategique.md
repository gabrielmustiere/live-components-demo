# 🧭 12. Positionnement stratégique (mindset lead dev)

## 🎯 Objectif du chapitre

Sortir de la technique pure et adopter un **regard de lead / architecte** : quand, pourquoi, comment introduire ces outils dans une équipe.

---

## Slide 12.1 — Quand choisir quoi ?

### Règle d'orientation rapide

| Contexte | Techno recommandée |
|----------|-------------------|
| **Petit projet / MVP** | Twig Components uniquement |
| **App CRUD / Backoffice / SaaS B2B** | Twig + Live Components |
| **App très interactive (éditeur, dashboard temps réel)** | Live + îlots React/Vue |
| **App avec mode offline / mobile lourde** | SPA complète (React/Vue + API) |

### Grille de questions à se poser

1. Est-ce que l'UI a besoin d'**état client riche** ? (undo/redo, draft…)
2. Est-ce que les **interactions doivent être instantanées** (ms, pas ×00ms) ?
3. Est-ce qu'on a besoin de fonctionner **offline** ?
4. Est-ce que l'équipe a **l'expertise front** pour maintenir du React/Vue ?

> Si on répond **non** à la plupart → **Symfony UX suffit (et gagne)**.

---

## Slide 12.2 — Stratégies d'adoption

### 🟢 Stratégie 1 : adoption progressive

1. **Phase 1** — Extraire le design system en Twig Components
   - On touche au markup existant petit à petit
   - Aucun risque fonctionnel
   - Gain immédiat : cohérence + maintenabilité

2. **Phase 2** — Remplacer les zones "Stimulus/Ajax bricolé" par du Live
   - Identifier les widgets JS custom (search, filtres, mini-forms)
   - Les réécrire en Live Component
   - Supprimer du JS

3. **Phase 3** — Standardiser sur de nouveaux patterns
   - Toute nouvelle UI interactive → Live Component par défaut
   - React uniquement pour les cas **explicitement justifiés**

### 🟠 Stratégie 2 : big bang

- Migrer toute l'UI d'un coup
- **Rarement recommandé** : risque élevé pour gain incertain
- Acceptable seulement sur une réécriture complète

---

## Slide 12.3 — Construire un design system Twig

### Pourquoi c'est le premier chantier à lancer

- **Investissement faible, ROI élevé**
- Fait monter l'équipe en compétence sur Twig Components
- Prépare le terrain pour l'adoption Live

### Arborescence recommandée

```
src/Twig/Components/
├── Atom/               # composants atomiques
│   ├── Button.php
│   ├── Badge.php
│   └── Icon.php
├── Molecule/           # composés
│   ├── Alert.php
│   ├── Card.php
│   └── FormField.php
├── Organism/           # blocs complexes
│   ├── Navbar.php
│   ├── ProductList.php
│   └── Sidebar.php
└── Live/               # Live Components
    ├── Counter.php
    ├── ProductSearch.php
    └── Cart.php

templates/components/
└── (même arborescence)
```

### Recommandations

- **Documenter** chaque composant (Storybook-like via ux-twig-component playground)
- **Typer fortement** les props (PHP 8 types, enums)
- **Tester** les composants critiques (rendu + comportement)
- **Versionner** le design system si plusieurs apps le partagent

---

## Slide 12.4 — Poser une gouvernance claire

### Règles à afficher dans le CONTRIBUTING / CLAUDE.md de l'équipe

1. Tout nouveau markup répété ≥ 2 fois → **Twig Component**
2. Toute interactivité nécessitant du JS → **d'abord** envisager Live, React seulement si justifié
3. Pas de `render(controller())` dans les nouveaux développements
4. Les extensions Twig restent pour les **helpers** purs (formatage), pas pour du markup
5. Validation des props : **types PHP**, pas de tableaux magiques

### Qui décide ?

- Un **architecte / lead** arbitre les cas limites
- Les devs livrent avec autonomie dans le cadre fixé

---

## Slide 12.5 — Le piège "React par défaut"

### Le coût caché de React dans une app Symfony "classique"

- **Deux stacks à maintenir** (PHP + JS)
- **Duplication de modèles** (DTO PHP + types TS)
- **Deux pipelines de build**
- **Deux équipes** souvent
- **Deux compétences** à recruter

### Quand ce coût est justifié

- UI réellement riche (collaborative, temps réel, offline…)
- Équipe dimensionnée pour ça
- ROI clair pour l'utilisateur final

### Quand ce coût n'est **pas** justifié

- Backoffice CRUD standard
- App interne à faible concurrence d'usage
- MVP où chaque heure compte

---

## 💬 Message clé

> **"En tant que lead dev, la question n'est pas *'comment intégrer Symfony UX ?'* mais *'jusqu'où peut-on aller sans quitter Symfony UX ?'*"**
> Plus on repousse la frontière React, plus on simplifie l'architecture.

---

## 🗣️ Narration (script oral)

> "Dans les équipes où je suis intervenu, le réflexe par défaut était souvent "on fait du React parce que c'est moderne". Mais quand on analyse objectivement les besoins, 80% des écrans sont du CRUD, du listing, du formulaire. Pour ça, Symfony UX n'est pas seulement *suffisant* — il est **meilleur** : moins de code, une seule équipe, une architecture unifiée. Mon rôle de lead, c'est de poser cette frontière clairement, et de la défendre."

---

## 🧭 Transition vers le chapitre 13

Concluons sur la **vision globale** et les pistes pour aller plus loin.
