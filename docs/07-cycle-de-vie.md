# 🔁 7. Cycle de vie d'un Live Component

## 🎯 Objectif du chapitre

Maîtriser le **flow technique** d'un Live Component — indispensable pour **debug, optimiser et bien architecturer**.

---

## Slide 7.1 — Flow technique (vue d'ensemble)

### Les 6 étapes

1. **Render initial** — HTML rendu côté serveur, sérialisation de l'état dans le DOM
2. **Interaction utilisateur** — clic, input, submit…
3. **Ajax call** — le client envoie l'état + l'action au serveur
4. **Hydratation + action** — PHP reconstruit le composant, exécute la méthode
5. **Re-render** — Twig régénère le markup du composant
6. **DOM patch** — morphdom applique les diffs côté client

---

## Slide 7.2 — Étape 1 : Render initial

### Ce qui se passe

- Le contrôleur Symfony rend la page classique
- Un `<twig:Counter count="0" />` est instancié
- Le markup est envoyé au navigateur **avec l'état sérialisé** dans des attributs HTML
- Stimulus prend le contrôle du composant côté client

### Exemple de markup généré

```html
<div
  data-controller="live"
  data-live-props-value="{&quot;count&quot;:0}"
  data-live-csrf-value="..."
>
  <p>Compteur : 0</p>
  <button data-action="live#action" data-live-action-param="increment">+1</button>
</div>
```

---

## Slide 7.3 — Étape 2 : Interaction utilisateur

- L'utilisateur clique sur `+1`
- Stimulus intercepte l'événement
- Il lit l'état actuel depuis le DOM
- Il prépare la requête Ajax

### À noter

- **Aucune logique métier en JS**
- Stimulus joue uniquement un rôle de **transport**

---

## Slide 7.4 — Étape 3 : Ajax call

### Payload envoyé

- L'**état actuel** du composant (ses `LiveProp`)
- Le **nom de l'action** à exécuter (`increment`)
- Les **arguments** éventuels
- Le **token CSRF** pour sécuriser l'appel

### Endpoint

- Une route interne gérée par le bundle `ux-live-component`
- Pas besoin d'écrire un contrôleur : c'est géré automatiquement

---

## Slide 7.5 — Étape 4 : PHP hydrate + exécute

### Côté serveur

1. Le bundle **reçoit la requête**
2. Il **hydrate** le composant : instancie la classe, remet les `LiveProp` à leur valeur
3. Il **vérifie** le CSRF, la sécurité, les contraintes de validation
4. Il **exécute** la `LiveAction` demandée (`increment()`)
5. Les `LiveProp` sont éventuellement mises à jour (`$this->count++`)

### Point clé

> Entre deux requêtes, **le composant n'existe pas** côté serveur. Il est **reconstruit à chaque fois** à partir de l'état sérialisé.

---

## Slide 7.6 — Étape 5 : Re-render Twig

- Le bundle appelle le rendu Twig du composant
- Le nouveau markup est produit
- Il est renvoyé en réponse (fragment HTML + nouvel état sérialisé)

---

## Slide 7.7 — Étape 6 : DOM patch

### Côté client

- La réponse est interprétée
- **morphdom** (ou équivalent) applique un **patch ciblé** au DOM existant
- Seuls les nœuds réellement modifiés sont remplacés
- Le focus, la position de scroll, les inputs non modifiés sont **préservés**

---

## 💬 Résumé en une phrase

> **"Server-driven UI"** : l'état vit côté serveur, chaque interaction déclenche un cycle hydrate → action → re-render → patch.

---

## ⚠️ Implications pour un lead dev

- **Aucun état implicite** entre deux requêtes → tout ce qui doit survivre doit être une `LiveProp`
- **Chaque action = un round-trip** → attention à la latence sur les interactions fréquentes (utiliser `debounce`)
- **Idempotence** souhaitable : une action peut être rejouée si le client retry
- **Taille de l'état** sérialisé dans le DOM → ne pas y mettre des entités complètes, préférer des IDs
- **Sécurité** : les `LiveProp` `writable` viennent du client, donc **à valider** comme n'importe quelle entrée utilisateur

---

## 🗣️ Narration (script oral)

> "Si vous retenez une seule chose de ce chapitre : **un Live Component est stateless côté serveur**. Il est reconstruit à chaque requête à partir de l'état sérialisé dans le DOM. Ça a deux conséquences majeures : primo, tout ce qui doit persister entre deux interactions doit être une `LiveProp` ; secundo, la sécurité se gère comme sur n'importe quel endpoint — ne jamais faire confiance à l'état qui revient du client."

---

## 🧭 Transition vers le chapitre 8

Maintenant qu'on comprend le modèle, **comparons-le** à ce qu'on avait avant et à ce que propose le front moderne.
