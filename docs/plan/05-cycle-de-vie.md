# 🔁 5. Cycle de vie d'un Live Component

> 🧭 **Chapitre deep-dive** — le plus technique du talk. Indispensable pour déboguer en prod, utile pour piloter l'architecture, **optionnel** en version courte (45 min) où l'on peut se contenter du **diagramme de la slide 7.1** avant de passer à la démo (ch. 8).

## 🎯 Objectif du chapitre

Maîtriser le **flow technique complet** d'un Live Component — indispensable pour **déboguer, optimiser et bien architecturer**.

Le chapitre 4 a posé *ce qu'est* un Live Component et *comment l'écrire*. Ici on entre dans le **moteur** : ce qui se passe vraiment entre le clic de l'utilisateur et le DOM patché. Trois questions structurantes :

1. **Qui fait quoi**, côté client et côté serveur, à quelle étape ?
2. **Quels hooks** permet au composant de s'accrocher au cycle ?
3. **Comment observer le cycle** quand quelque chose cloche ?

> 💡 **Parcours rapide (audience non-ops)** : slides 7.1 (séquence) → 7.8 (hooks) → 7.10 (debugger). Le reste peut être référencé comme ressource.

---

## Slide 5.1 — Diagramme de séquence complet

```
Client (navigateur)              Réseau              Serveur PHP
        │                           │                     │
        │  ── requête initiale ──────────────────────────►│
        │                           │       mount() / PostMount
        │                           │       PreDehydrate
        │                           │       Twig render
        │◄── HTML + état DOM ────────────────────────────  │
        │                           │                     │
        │  [clic / input / debounce]│                     │
        │  Stimulus lit le DOM      │                     │
        │  ── POST /_components ───►│                     │
        │    props + action         │── Ajax JSON ───────►│
        │                           │       Instantiation
        │                           │       Hydration LiveProp
        │                           │       PostHydrate
        │                           │       CSRF check
        │                           │       LiveAction()
        │                           │       PreReRender
        │                           │       Twig re-render
        │                           │       PreDehydrate
        │                           │◄─ HTML + nouvel état  │
        │◄─ réponse HTML ──────────  │                     │
        │   morphdom patch           │                     │
        │   JS: render:finished      │                     │
```

### Les deux cycles à distinguer

| Cycle | Déclencheur | Hooks impliqués |
|-------|-------------|-----------------|
| **Render initial** | Requête HTTP classique | `mount()`, `#[PostMount]`, `#[PreDehydrate]` |
| **Re-render Ajax** | Interaction utilisateur | `#[PostHydrate]`, `#[PreReRender]`, `#[PreDehydrate]` |

> ⚠️ **À retenir** : entre deux requêtes, **le composant n'existe pas** côté serveur. Il est **reconstruit à chaque cycle** à partir de l'état sérialisé dans le DOM.

---

## Slide 5.2 — Étape 1 : Render initial (mount)

### Ce qui se passe côté serveur

1. Le contrôleur Symfony rend la page avec `<twig:Counter count="0" />`
2. Le bundle **instancie** la classe du composant, les services sont **autowirés** dans le constructeur
3. **`mount()`** est appelée si elle existe (ou les props sont passées directement)
4. **`#[PostMount]`** est déclenché — utile pour initialiser un état calculé
5. Twig produit le markup du composant
6. **`#[PreDehydrate]`** s'exécute — dernier moment pour transformer les props avant sérialisation
7. Les `LiveProp` sont **sérialisées** dans le DOM

### Exemple de markup généré

```html
<div
  data-controller="live"
  data-live-url-value="/_components/Counter"
  data-live-props-value="{&quot;count&quot;:0}"
  data-live-fingerprint-value="abc123"
>
  <p>Compteur : 0</p>
  <button data-action="live#action" data-live-action-param="increment">+1</button>
</div>
```

### Les attributs `data-*` expliqués

| Attribut | Rôle |
|----------|------|
| `data-controller="live"` | Active le Stimulus controller du bundle |
| `data-live-url-value` | Endpoint Ajax du composant |
| `data-live-props-value` | État courant sérialisé en JSON (toutes les `LiveProp`) |
| `data-live-fingerprint-value` | Hash de l'état pour détecter les conflits |

---

## Slide 5.3 — Étape 2 : Interaction utilisateur

- L'utilisateur clique sur `+1`, saisit dans un `data-model`, soumet un formulaire…
- **Stimulus** intercepte l'événement via les `data-action`
- Il lit l'état actuel depuis `data-live-props-value`
- Il attend éventuellement le debounce (150 ms par défaut pour `data-model`)
- Il construit le payload Ajax et déclenche la requête

### À noter

- **Aucune logique métier en JS** — Stimulus est uniquement un **mécanisme de transport**
- Pour les `data-model`, le client ne fait que signaler "cette prop a changé avec cette valeur"
- Le serveur reste la **seule autorité** sur la logique métier

---

## Slide 5.4 — Étape 3 : Le payload Ajax

### Endpoint réel

```
POST /_components/Counter
Accept: application/vnd.live-component+html
Content-Type: application/json
```

### Structure du payload

```json
{
  "props": {
    "count": 0
  },
  "updated": {
    "query": "symfony"
  },
  "actions": [
    {
      "name": "increment",
      "args": {}
    }
  ]
}
```

### Ce que contient chaque champ

| Champ | Contenu |
|-------|---------|
| `props` | L'**état complet** du composant (toutes les `LiveProp` courantes) |
| `updated` | Les **props modifiées** côté client via `data-model` (delta) |
| `actions` | La ou les `LiveAction` à exécuter, avec leurs arguments |

### Sécurité : pas de token CSRF custom

Le bundle s'appuie sur le **same-origin policy** du navigateur et le header `Accept` personnalisé pour s'assurer que la requête vient du navigateur légitime — pas d'un appel CSRF cross-domain.

> ⚠️ **Ce qui se passe si on falsifie `props`** : le client peut envoyer n'importe quelle valeur pour les `LiveProp` `writable`. C'est une **entrée utilisateur non fiable**, exactement comme un champ de formulaire. Valider systématiquement.

---

## Slide 5.5 — Étape 4 : Hydratation PHP

### La séquence côté serveur

```
1. Requête reçue sur /_components/Counter
2. Bundle instancie la classe + autowire les services
3. LiveProps hydratées depuis le payload JSON
4. → #[PostHydrate] déclenché
5. LiveAction exécutée (increment())
6. Les LiveProps sont éventuellement modifiées
```

### Le point clé

Le composant est **stateless** entre deux requêtes. Il n'y a aucun objet "Counter" qui attend en mémoire côté serveur. À chaque cycle, la classe est **re-instanciée** et les props sont **re-hydratées** depuis ce qui vient du client.

```php
#[AsLiveComponent]
final class Counter
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $count = 0;

    #[PostHydrate]
    public function afterHydration(): void
    {
        // Ici : les LiveProp ont leurs valeurs issues du payload client.
        // Exemple : corriger une valeur hors limites avant d'exécuter l'action.
        if ($this->count < 0) {
            $this->count = 0;
        }
    }

    #[LiveAction]
    public function increment(): void
    {
        $this->count++;
    }
}
```

### Hydratation custom pour les objets

Pour les types non-scalaires (entités, DTOs, value objects), le bundle passe par le **Symfony Serializer**. On peut surcharger ce comportement avec `hydrateWith` / `dehydrateWith` (cf. chapitre 4, slide 4.6) — et c'est souvent indispensable pour les entités Doctrine.

---

## Slide 5.6 — Étape 5 : Re-render Twig

### La séquence

```
1. LiveAction exécutée (props mises à jour)
2. → #[PreReRender] déclenché (dernier moment avant le rendu)
3. Twig re-génère le markup complet du composant
4. → #[PreDehydrate] déclenché
5. Les LiveProp sont re-sérialisées dans la réponse
6. Réponse HTML envoyée au client
```

### `#[PreReRender]` — le hook du "avant rendu"

```php
#[PreReRender]
public function beforeRender(): void
{
    // Calculer des valeurs dérivées coûteuses juste avant le rendu,
    // sans qu'elles soient des LiveProp.
    $this->formattedCount = number_format($this->count, 0, ',', ' ');
}
```

> `#[PreReRender]` est **uniquement** déclenché lors d'un re-render Ajax, pas lors du render initial. Pour le render initial, utiliser `#[PostMount]`.

### `#[PreDehydrate]` — le hook du "avant sérialisation"

```php
#[PreDehydrate]
public function beforeDehydrate(): void
{
    // Transformer une valeur avant qu'elle parte dans le DOM / la réponse.
    // Exemple : convertir un DateTimeImmutable en timestamp pour la sérialisation.
}
```

---

## Slide 5.7 — Étape 6 : DOM patch (morphdom)

### Ce que reçoit le client

La réponse est du **HTML pur** — le markup complet du composant (pas un diff JSON, pas un fragment partiel).

### Comment morphdom travaille

1. Il compare l'**arbre DOM existant** avec le **nouvel HTML reçu**
2. Il met à jour **uniquement les nœuds qui diffèrent** (valeur de texte, attributs)
3. Il **préserve** ce qui ne doit pas changer :
   - Le focus (l'input en cours de saisie reste actif)
   - La position de scroll
   - Les inputs non modifiés
   - Les Stimulus controllers déjà attachés

### Contrôler le comportement du morphing

```html
{{# Exclure un élément du morphing (ex : une animation JS, un éditeur riche) #}}
<div data-live-ignore>
    Ce contenu n'est jamais touché par morphdom.
</div>

{{# Forcer le remplacement complet (morphdom ignoré pour cet élément) #}}
<div data-live-replace>
    Remplacement total au prochain render.
</div>
```

### Les clés stables sur les listes

```twig
{{# Sans id : morphdom peut réutiliser un nœud pour le mauvais item → bugs visuels #}}
{% for item in items %}
    <li>{{ item.name }}</li>
{% endfor %}

{{# Avec id stable : morphdom identifie chaque item correctement #}}
{% for item in items %}
    <li id="item-{{ item.id }}">{{ item.name }}</li>
{% endfor %}
```

---

## Slide 5.8 — Les hooks du cycle de vie

Vue d'ensemble des quatre hooks côté PHP :

| Hook | Render initial | Re-render Ajax | Moment d'exécution |
|------|:--------------:|:--------------:|-------------------|
| `#[PostMount]` | ✅ | ❌ | Après l'instanciation + mount() |
| `#[PostHydrate]` | ❌ | ✅ | Après hydratation des LiveProp |
| `#[PreReRender]` | ❌ | ✅ | Juste avant le re-render Twig |
| `#[PreDehydrate]` | ✅ | ✅ | Avant la sérialisation des props |

### Cas d'usage typiques

```php
#[AsLiveComponent]
final class ProductSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    /** @var Product[] */
    public array $results = [];

    #[PostMount]
    public function initializeResults(): void
    {
        // Pré-remplir les résultats au render initial,
        // sans que $results soit une LiveProp (trop lourd à sérialiser).
        $this->results = $this->loadResults();
    }

    #[PostHydrate]
    public function afterHydration(): void
    {
        // Après chaque cycle Ajax : recharger les résultats.
        // On ne stocke pas $results dans une LiveProp — trop coûteux.
        // On recalcule à chaque fois.
        $this->results = $this->loadResults();
    }

    #[PreReRender]
    public function beforeRender(): void
    {
        // Dernier moment pour préparer un état dérivé
        // (ex : paginer $this->results, appliquer un sort).
    }

    #[PreDehydrate]
    public function beforeDehydrate(): void
    {
        // $results ne doit pas partir dans le DOM :
        // on s'assure qu'elle est vide avant la sérialisation.
        $this->results = [];
    }

    private function loadResults(): array
    {
        return $this->productRepository->search($this->query);
    }
}
```

### Priorité des hooks

Quand plusieurs hooks du même type existent (plusieurs classes traits, plusieurs méthodes) :

```php
#[PostHydrate(priority: 10)]   // exécuté en premier (priorité haute = avant)
public function importantHydration(): void {}

#[PostHydrate(priority: 0)]    // exécuté après
public function secondaryHydration(): void {}
```

---

## Slide 5.9 — Les hooks JS côté client

Le bundle expose une API JavaScript pour s'accrocher aux événements du cycle côté client. Utile pour des intégrations ponctuelles sans écrire un Stimulus controller complet.

```javascript
import { getComponent } from '@symfony/ux-live-component';

const element = document.querySelector('[data-controller="live"]');
const component = await getComponent(element);

// Cycle de vie
component.on('connect', () => { /* Stimulus controller connecté */ });
component.on('disconnect', () => { /* Composant retiré du DOM */ });

// Requête Ajax
component.on('render:started', (html, response, controls) => {
    // html = le HTML reçu du serveur
    // controls.preventDefault() pour annuler le patch DOM
});
component.on('render:finished', () => { /* Après morphdom */ });
component.on('response:error', (backendResponse, controls) => {
    // Intercepter une erreur serveur (500, 422...)
});

// Loading state
component.on('loading.state:started', (element, request) => {});
component.on('loading.state:finished', (element) => {});

// Modèle
component.on('model:set', (model, value) => {
    // Déclenché quand une LiveProp change côté client
});
```

> Ces hooks sont destinés à des **intégrations ponctuelles** (analytics, bibliothèques tierces, animations). Ne pas y mettre de logique métier — elle reste en PHP.

---

## Slide 5.10 — Debugger le cycle

### Le profiler Symfony

Chaque requête Ajax Live Component apparaît dans la barre debug Symfony avec :
- La liste des **props** reçues et hydratées
- L'**action** exécutée
- Les **erreurs de validation**
- Le temps d'exécution

Pour y accéder : `/_profiler` → chercher les requêtes `POST /_components/*`.

### L'onglet réseau du navigateur

Filtrer par `/_components` dans les DevTools :

```
Request Headers:
  Accept: application/vnd.live-component+html
  Content-Type: application/json

Request Payload:
  {"props":{"count":3},"updated":{},"actions":[{"name":"increment","args":{}}]}

Response Headers:
  Content-Type: text/html; charset=UTF-8

Response Body:
  <div data-controller="live" data-live-props-value="{&quot;count&quot;:4}" ...>
    <p>Compteur : 4</p>
    ...
  </div>
```

### Logs Symfony

```bash
# Voir toutes les requêtes Live Component en dev
tail -f var/log/dev.log | grep live_component
```

### Astuce : inspecter l'état sérialisé

```javascript
// Dans la console navigateur, lire l'état courant d'un composant :
const el = document.querySelector('[data-controller="live"]');
JSON.parse(el.dataset.livePropsValue);
// → {count: 4}
```

---

## Slide 5.11 — Gestion des erreurs dans le cycle

### Erreur dans une LiveAction

Si une `LiveAction` lance une exception, le bundle retourne une **réponse 500**. Côté client, `render:error` est déclenché. Par défaut, une erreur console s'affiche.

**Bonne pratique** : ne pas laisser une action exploser sans handler. Attraper les erreurs métier dans l'action et les traduire en état du composant :

```php
#[LiveAction]
public function save(): void
{
    try {
        $this->orderService->place($this->order);
        $this->success = true;
    } catch (InsufficientStockException $e) {
        $this->errorMessage = 'Stock insuffisant pour cette commande.';
    }
}
```

### Erreur de validation (via ValidatableComponentTrait)

La validation Symfony est intégrée — les violations sont automatiquement injectées dans le template via `_errors`. Le cycle ne s'interrompt pas : le composant se re-rende avec les erreurs affichées.

### Conflit de fingerprint (état obsolète)

Si le client envoie un `fingerprint` qui ne correspond plus à l'état courant (page ouverte dans deux onglets, session expirée, déploiement entre deux requêtes), le bundle déclenche un **re-render forcé** — le composant est re-rendu depuis son état serveur plutôt que depuis le payload client.

### Timeout réseau / requête annulée

Si une requête Ajax échoue côté réseau, Stimulus retry automatiquement selon une politique de backoff. L'événement `response:error` permet d'intercepter et d'afficher un feedback à l'utilisateur.

---

## 💬 Message clé

> **"Stateless serveur, stateful DOM."** Un Live Component est reconstruit à chaque requête à partir de ce que le client lui renvoie. Ce modèle simplifie radicalement l'architecture — au prix d'un invariant à respecter : **tout ce qui doit survivre entre deux interactions doit être une `LiveProp`**.

---

## ⚠️ Implications pour un lead dev

- **Aucun état implicite** entre deux requêtes → variables d'instance non `LiveProp` : perdues à chaque cycle. Ne pas compter dessus.
- **`LiveProp` `writable` = entrée utilisateur** → valider comme n'importe quel champ de formulaire. Le client peut envoyer n'importe quoi.
- **Chaque action = un round-trip** → debounce obligatoire sur les inputs texte (`debounce(300)|query`), `on(change)` pour les selects.
- **Taille de l'état sérialisé** → ne pas stocker des entités complètes dans une `LiveProp`. Stocker un ID et recharger en `#[PostHydrate]`.
- **Listes sans id stable** → morphdom crée des bugs visuels subtils. Toujours ajouter `id="item-{{ item.id }}"` sur les éléments de liste réordonnables.
- **`data-live-ignore`** → pour les zones gérées par une bibliothèque JS tierce (éditeur WYSIWYG, carte Leaflet) : protéger du morphdom.
- **Idempotence** souhaitée → une action peut être rejouée si le client retry. Les actions non idempotentes (paiement, envoi d'email) méritent une protection explicite (token d'idempotence, vérification de statut).

---

## 🗣️ Narration (script oral)

> "Si vous retenez une seule chose de ce chapitre : un Live Component est **stateless côté serveur**. Il n'y a pas d'objet qui attend entre deux requêtes. À chaque cycle Ajax, la classe est re-instanciée, les props sont re-hydratées depuis ce que le client a renvoyé, l'action s'exécute, Twig re-rend, et l'état repart dans le HTML.
>
> Ça a des conséquences directes : primo, tout ce qui doit persister doit être une `LiveProp`. Secundo, tout ce qui vient du client est une entrée utilisateur — pas de confiance implicite. Et tertio, les hooks du cycle de vie — `PostHydrate`, `PreReRender`, `PreDehydrate` — sont vos outils pour vous accrocher au bon moment sans casser le modèle.
>
> Pour déboguer, l'onglet réseau du navigateur est votre meilleur ami : chaque interaction est une requête `POST /_components/...` lisible et inspectable. Et le profiler Symfony vous donne la vue serveur de chaque cycle. Quand un composant se comporte bizarrement, commencez par là."

---

## 🧭 Transition vers le chapitre 6

On vient d'entrer dans **le moteur** : ce qui se passe à chaque cycle, ce que coûte chaque interaction, ce que garantit le bundle. Avant d'appliquer tout ça à des cas concrets, on **respire une seconde** : deux démos courtes, en live, pour voir tout ce vocabulaire s'exécuter dans un navigateur. C'est l'objet du chapitre 6.
