# ⚖️ 8. Comparaison — Twig Component vs Live Component vs React/Vue

## 🎯 Objectif du chapitre

Positionner **clairement** les trois approches pour savoir **quand choisir quoi**.

---

## Slide 8.1 — Tableau comparatif

| Feature              | Twig Component | Live Component    | React / Vue       |
|----------------------|----------------|-------------------|-------------------|
| **Rendering**        | Server         | Server + Ajax     | Client            |
| **State**            | Statique       | Serveur synchronisé | Client          |
| **JS requis**        | ❌              | ❌ (Stimulus inclus) | ✅               |
| **Build tool**       | ❌              | ❌                 | ✅                |
| **SEO**              | ✅ natif        | ✅ natif           | ⚠️ SSR nécessaire |
| **Offline**          | ❌              | ❌                 | ✅ possible       |
| **Latence interaction** | N/A         | Réseau            | Local (instant)   |
| **Complexité**       | Faible         | Moyenne           | Élevée            |
| **Courbe d'apprentissage** | Faible   | Moyenne           | Élevée            |
| **Écosystème**       | Twig / Symfony | Twig / Symfony    | NPM (énorme)      |

---

## Slide 8.2 — Où brille chaque approche

### Twig Component brille quand…

- L'UI est **statique** ou rendue au chargement
- On construit un **design system** (boutons, cards, alerts, badges…)
- On veut **factoriser du markup** sans complexité
- Le rendu dépend de **données serveur** (BDD, config, user connecté)

### Live Component brille quand…

- L'UI a besoin d'**interactivité modérée** (search, filtres, forms multi-étapes)
- On veut **éviter de dupliquer la logique** front/back
- La **latence réseau est acceptable** (apps internes, backoffices…)
- On veut rester dans **l'écosystème Symfony**

### React / Vue brille quand…

- L'UI est **très interactive** (drag & drop, canvas, temps réel)
- On a besoin d'**état client riche** (undo/redo, optimistic UI)
- L'app doit fonctionner **offline** ou **sur mobile natif**
- L'équipe a déjà une **expertise front** forte

---

## Slide 8.3 — Matrice de décision

```
                    Interactivité élevée
                           ▲
                           │
                 ┌─────────┼─────────┐
                 │         │         │
                 │  Live   │  React  │
                 │ Comp.   │  / Vue  │
                 │         │         │
   Server ───────┼─────────┼─────────┼───── Client
   driven        │         │         │
                 │  Twig   │  (cas   │
                 │  Comp.  │  rare)  │
                 │         │         │
                 └─────────┼─────────┘
                           │
                           ▼
                    Interactivité faible
```

---

## Slide 8.4 — Les pièges à éviter

### ❌ "On met React partout par réflexe"

- Pour un backoffice CRUD, c'est **overkill**
- Duplication logique back/front
- Dette technique future

### ❌ "On met Live Component partout"

- Pour une UI très interactive (chat, canvas), la latence devient visible
- Le serveur devient un goulot d'étranglement

### ❌ "On reste sur Twig include par habitude"

- On passe à côté de la réutilisabilité
- On ne gagne rien sur la maintenabilité

---

## 💬 Message clé

> **Twig Component, Live Component et SPA ne sont pas concurrents — ils sont complémentaires.**
> Un même projet peut (et doit souvent) combiner les trois selon les zones.

---

## 🗣️ Narration (script oral)

> "Ce qu'il faut comprendre, c'est que Symfony UX ne dit pas "n'utilisez plus jamais React". Il dit "pour 80% de vos besoins, vous n'en avez pas besoin". La vraie compétence d'un lead dev, c'est de savoir **où placer la frontière** : un composant Twig pour le design system, du Live pour les formulaires dynamiques, et du React uniquement pour les zones où la latence ou l'état client l'exigent vraiment."

---

## 🧭 Transition vers le chapitre 9

Passons au concret : **quels cas d'usage** pour chaque brique ?
