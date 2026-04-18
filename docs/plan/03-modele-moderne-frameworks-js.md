# ⚛️ 3. Le modèle moderne — Les frameworks JS

## 🎯 Objectif du chapitre

Comprendre **pourquoi React / Vue ont gagné** et identifier les concepts à ramener côté serveur.

---

## Slide 3.1 — Pourquoi React / Vue ont gagné

### Les idées fortes

- **UI = composition de composants** (et non plus des pages monolithiques)
- **State local** : chaque composant gère ses données (props + state)
- **Réactivité** : la vue se met à jour automatiquement quand l'état change
- **Encapsulation** logique + template dans une même unité

### Exemple mental

```jsx
<Button label="Acheter" onClick={handleBuy} />
<Card user={currentUser} />
<ProductList products={products} />
```

Une page n'est plus un gros template : c'est un **arbre de composants**.

---

## Slide 3.2 — Les concepts clés à retenir

| Concept | Rôle |
|---------|------|
| **Props** | Données entrantes (contrat d'entrée, immuables) |
| **State** | État interne du composant (mutable) |
| **Render** | Fonction pure : `(props, state) → UI` |
| **Events** | Interactions utilisateur (click, input, submit…) |
| **Composition** | Un composant peut en contenir d'autres |

### Pourquoi c'est puissant

- **Raisonnement local** : on comprend un composant sans lire toute l'app
- **Testable** en isolation
- **Réutilisable** dans des contextes différents
- **Prévisible** : même props + même state → même rendu

---

## Slide 3.3 — Le prix à payer

- Build tools (webpack, vite…)
- Bundle JS envoyé au client (perf, SEO)
- Duplication de logique (validation front + back)
- Complexité pour des besoins simples (CRUD)

### Le dilemme

> **"Est-ce qu'on a besoin d'une SPA pour afficher une liste de produits filtrable ?"**

Pour beaucoup d'apps Symfony : **non**. Mais on veut quand même le **modèle composant**.

---

## 💬 Message clé

> **"Une page = un arbre de composants."**
> C'est **ce modèle mental** qu'on veut, pas forcément tout l'écosystème JS qui va avec.

---

## 🗣️ Narration (script oral)

> "React, Vue, Svelte… ils ont tous convergé vers la même idée : penser l'UI comme une composition de petites unités autonomes. Cette idée est **indépendante du langage** : rien n'empêche de la porter côté PHP. C'est exactement ce que Symfony UX a fait, en gardant Twig et PHP, sans imposer un bundle JS côté client."

---

## 🧭 Transition vers le chapitre 4

Voyons comment Symfony UX a **traduit ces concepts côté serveur** sans sacrifier la DX Symfony.
