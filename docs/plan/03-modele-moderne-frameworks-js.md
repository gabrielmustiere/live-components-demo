# ⚛️ 3. Le modèle moderne — Les frameworks JS

## 🎯 Objectif du chapitre

Comprendre **pourquoi React / Vue ont gagné**, identifier les **concepts précis** qui font leur force, et décider **lesquels méritent d'être portés côté serveur**.

Questions structurantes :

- Comment est-on passé de jQuery à React ? Qu'est-ce qui a changé conceptuellement ?
- C'est quoi, exactement, un "composant" ? Quelles sont ses propriétés invariantes ?
- Qu'est-ce qu'on **veut** récupérer de ce modèle — et qu'est-ce qu'on peut laisser côté JS ?
- En quoi ce modèle mental répond-il aux **trois douleurs** identifiées au chapitre 2 ?

---

## Slide 3.1 — La généalogie : comment on en est arrivé là

### L'ère jQuery (2006–2013)

```js
// La page entière est le "composant"
$('#btn-buy').on('click', function () {
    $.ajax('/cart/add', { data: { id: 42 } }).done(function (html) {
        $('#cart-widget').html(html);
    });
});
```

Ça marche. Mais dès que la page grossit :
- **État éparpillé** dans le DOM lui-même (classes CSS, attributs data, variables globales)
- **Couplage implicite** : n'importe qui peut modifier n'importe quoi
- Aucune notion d'unité — une page, un grand script

### La transition Backbone / Angular 1 (2010–2015)

Première tentative de structurer : des modèles, des vues, des contrôleurs. Mieux, mais :
- Angular 1 introduit la notion de *two-way binding* et de directives (composants rudimentaires)
- Backbone donne des modèles observables, mais le rendu reste à la charge du développeur

**Le vrai tournant :** Angular 1 était lourd, opaque, difficile à déboguer. L'équipe React (Facebook, 2013) tire une conclusion radicale : *le two-way binding est le problème, pas la solution*.

### La convergence React / Vue / Svelte (2013→)

React pose un postulat simple :

```
UI = f(state)
```

La vue est une **fonction pure de l'état**. Quand l'état change, la vue se recalcule. Pas de synchronisation manuelle, pas de DOM mutable partagé.

Vue et Svelte reprendront ce modèle avec des variantes syntaxiques, mais la **même idée centrale** : composer une interface à partir d'unités autonomes qui gèrent leur propre état.

---

## Slide 3.2 — Anatomie d'un composant

Tous les frameworks modernes convergent vers la même structure de base :

```jsx
// Un composant React (simplifié)
function ProductCard({ name, price, stock }) {   // ← props (entrées typées)
    const [qty, setQty] = useState(1);            // ← state (état interne)

    function handleBuy() {                        // ← event handler
        addToCart({ name, price, qty });
    }

    return (                                      // ← render : (props + state) → UI
        <div className="card">
            <h3>{name}</h3>
            <p>{price} € · {stock > 0 ? 'En stock' : 'Épuisé'}</p>
            <input type="number" value={qty} onChange={e => setQty(e.target.value)} />
            <button onClick={handleBuy} disabled={stock === 0}>Acheter</button>
        </div>
    );
}
```

```jsx
{/* Composition : une page = un arbre de composants */}
<Page>
    <Header cart={cart} />
    <ProductList>
        <ProductCard name="Clavier" price={89} stock={3} />
        <ProductCard name="Souris"  price={45} stock={0} />
    </ProductList>
    <Footer />
</Page>
```

### Le flux de données

```
            props (lecture seule)
Parent ─────────────────────────────▶ Composant enfant
                                            │
                                    state (interne,
                                    mutable par le composant)
                                            │
                                            ▼
                                      render()
                                            │
                                            ▼
                                    Mise à jour du DOM
                                            │
                             events │ (callbacks vers le parent)
                                    ▼
                                  Parent (remonte l'info)
```

Les données **descendent** (props), les événements **remontent** (callbacks). Ce flux **unidirectionnel** est ce qui rend le modèle raisonnablement prévisible.

---

## Slide 3.3 — Les cinq concepts clés

| Concept | Définition | Propriété essentielle |
|---------|-----------|----------------------|
| **Props** | Données passées au composant par son parent | Immuables dans le composant, contrat d'entrée explicite |
| **State** | État interne au composant | Mutable localement, déclenche un re-render si modifié |
| **Render** | Fonction `(props, state) → UI` | Pure : même entrées = même sortie, sans effets de bord |
| **Events** | Interactions utilisateur remontées vers le parent | Couplage minimal : le parent décide de la réaction |
| **Composition** | Un composant peut contenir d'autres composants | C'est ce qui crée l'arbre de la page entière |

### Pourquoi chacun est important

**Props typées** → le composant documente ses attentes. L'IDE peut les valider. On ne passe plus un tableau associatif flou.

**State local** → l'état ne vit pas dans le DOM, il vit dans une variable contrôlée. Le DOM est une *conséquence* de l'état, pas sa source de vérité.

**Render pur** → on peut raisonner sur un composant en isolation. Pas besoin de lire toute l'app pour comprendre ce qu'il affiche.

**Events remontants** → le parent garde le contrôle. Un composant n'a pas d'effets de bord cachés sur ses voisins.

**Composition** → on décompose l'interface en petites unités compréhensibles, testables séparément, réutilisables dans d'autres contextes.

---

## Slide 3.4 — Résonance avec les trois douleurs du chapitre 2

Le chapitre 2 avait identifié **trois douleurs transversales** dans l'écosystème Symfony historique. Ce modèle composant y répond directement :

| Douleur (chapitre 2) | Réponse du modèle composant |
|----------------------|-----------------------------|
| **Pas de contrat de typage** entre la vue et ses données | Props explicites et typées → le composant déclare ce qu'il attend |
| **Pas d'unité d'organisation** — logique/markup/comportement disséminés | Un composant = une unité : logique + template + comportement colocalisés |
| **Pas de réactivité server-driven** — synchronisation état client/serveur impossible | State + render : un état unique fait autorité, la vue en est une projection |

> ⚠️ **À retenir** : ces concepts sont **indépendants du langage**. React les implémente en JS — mais rien, conceptuellement, n'empêche de les porter en PHP. C'est exactement le pari de Symfony UX.

---

## Slide 3.5 — Le prix à payer (côté JS)

Avant de conclure que le modèle composant est la réponse à tout, il faut nommer ce qu'il coûte dans sa version JS :

### Complexité infrastructurelle

- **Build tools** obligatoires : webpack, Vite, babel, tree-shaking…
- **Bundle JS** envoyé au client : souvent 200–500 KB pour une SPA modeste
- **Hydration** côté client : le HTML serveur est re-rendu en JS avant d'être interactif (coût à froid)

### Duplication de logique

```
Validation côté PHP  ←──────────────────────────────────────────┐
                                                                 │ Les deux doivent
Validation côté JS   ←──────────────────────────────────────────┘ rester synchronisés
```

La règle "email obligatoire, max 255 caractères" vit **deux fois** : dans le contrôleur Symfony et dans le formulaire React. Quand l'une change, l'autre doit suivre manuellement.

### Inadéquation avec des besoins simples

```
CRUD d'entités + filtres + pagination
        ≠
Application bancaire temps réel avec canvas interactif
```

Pour 80 % des apps Symfony — formulaires, tableaux filtrables, workflows CRUD — une SPA complète est une **sur-ingénierie**. On embarque React pour afficher une liste de produits filtrables, et on se retrouve à gérer `useEffect`, `useCallback`, un store global, et une API REST dédiée.

### Le dilemme

> **"Est-ce qu'on a besoin d'une SPA pour afficher une liste de produits filtrable ?"**

Pour beaucoup d'apps Symfony : **non**. Mais on veut quand même le **modèle composant**.

---

## Slide 3.6 — Ce qu'on garde, ce qu'on laisse

Le modèle composant est une idée. La SPA est une implémentation. Les deux ne sont pas liés.

| Ce qu'on **veut récupérer** | Ce qu'on peut **laisser côté JS** |
|-----------------------------|-----------------------------------|
| Props typées et explicites | Virtual DOM / reconciliation |
| Unité logique = classe + template | Bundle JS de plusieurs MB |
| State comme source de vérité | Hydration côté client |
| Composition (arbre de composants) | Build tools (webpack, Vite…) |
| Réactivité (état → vue automatique) | Double codebase PHP + JS |
| Raisonnement local sur un composant | Store global (Redux, Pinia…) |

**Le modèle mental à retenir :**

```
Page = arbre de composants
Composant = classe (logique) + template (rendu) + props (contrat)
État = source de vérité, la vue en est une projection
```

C'est **ce modèle**, porté en PHP/Twig, sans imposer de JS côté client au-delà du strict nécessaire. Symfony UX va le rendre possible.

---

## 💬 Message clé

> **"Une page = un arbre de composants. Ce modèle mental est indépendant du langage."**
> On veut la **composition, les props typées, la réactivité** — pas forcément tout l'écosystème JS qui va avec.

---

## 🗣️ Narration (script oral)

> "React, Vue, Svelte… on pourrait passer des heures à comparer leurs syntaxes. Mais ce qui importe ici, c'est ce sur quoi ils ont **tous convergé** : une UI se compose de petites unités autonomes, avec un contrat d'entrée explicite (les props), un état interne optionnel, et un rendu qui est une **fonction pure** de ces deux choses. Même entrées, même sortie. Toujours.
>
> Ce que cette idée a résolu, c'est exactement les trois douleurs qu'on avait listées au chapitre précédent. Plus besoin de chercher 'où vit la logique de ce bloc' — elle vit dans le composant. Plus de tableau associatif flou passé à l'include — on a des props typées. Plus de state dispersé dans le DOM — on a une variable d'état contrôlée.
>
> Maintenant, est-ce qu'on a besoin de React pour ça ? Non. Ces concepts sont **indépendants du langage**. Et c'est exactement le pari de Symfony UX : prendre les idées qui marchent, les traduire en PHP et en Twig, et les rendre accessibles à une équipe qui n'a pas envie de maintenir une double codebase. Le modèle composant, oui. La SPA, uniquement si nécessaire."

---

## 🧭 Transition vers le chapitre 4

On a le modèle mental. On sait ce qu'on veut garder et ce qu'on veut éviter. Voyons maintenant comment Symfony UX a **traduit ces concepts côté PHP** — son histoire, sa philosophie, et la cartographie des bundles qui composent l'initiative.
