# 🧩 4. Symfony UX — La réponse côté PHP

## 🎯 Objectif du chapitre

Présenter la **vision** Symfony UX, son **histoire**, son **architecture d'ensemble** et **positionner** le projet dans l'écosystème PHP/JS.

Après le chapitre 2 (les douleurs historiques côté Symfony) et le chapitre 3 (ce que le front moderne nous a appris), ce chapitre fait la **synthèse** : comment Symfony répond, sans jeter Twig ni embrasser le tout-SPA. On va répondre à quatre questions :

1. **D'où vient** Symfony UX ? (contexte historique, trigger)
2. **Quel est le postulat** philosophique du projet ?
3. **Quelle est la cartographie** des bundles et comment communiquent-ils ?
4. **Où Symfony UX se positionne-t-il** par rapport à Livewire, LiveView, HTMX, et aux SPAs ?

> ⚠️ **À retenir** : Symfony UX n'est **pas un framework**. C'est une **initiative**, un **parapluie** qui regroupe une **collection de bundles** indépendants, partageant un socle commun (Stimulus bridge + AssetMapper) et une **philosophie**.

---

## 🧭 Vue d'ensemble — la grille d'évaluation appliquée

Reprenons la grille des cinq critères du chapitre 2 et regardons ce que **chaque brique** Symfony UX apporte :

| Brique | Logique PHP | Template séparé | Props typées | État | Interactivité |
|--------|:-----------:|:---------------:|:------------:|:----:|:-------------:|
| **Twig Components** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Live Components** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Stimulus** | ❌ | ⚠️ | ⚠️ | ⚠️ client | ✅ |
| **Turbo** | n/a | n/a | n/a | n/a | ✅ navigation |

C'est la **combinaison** qui couvre tout le tableau du chapitre 2. On va le construire pièce par pièce.

---

## Slide 4.1 — Petit historique : d'où vient Symfony UX ?

### Contexte

- **Avant 2020** : Symfony est excellent côté backend, mais sur la couche front interactive, l'écosystème reste un patchwork (cf. chapitre 2).
- **2020** : Caleb Porzio publie **Livewire** (Laravel) → succès immédiat. Le modèle "composant PHP réactif" devient crédible.
- **2020** : DHH (Basecamp) sort **Hotwire** (Turbo + Stimulus) → la voie "HTML-over-the-wire" prend de l'ampleur.
- **2021** : Phoenix LiveView (Elixir) inspire toute une génération avec son modèle server-driven.

### Le déclic Symfony UX

- **Décembre 2020** : Fabien Potencier annonce **Symfony UX** comme une **initiative** transversale.
- **Postulat de départ** : "Symfony a tout pour bien faire — sauf la dernière brique, la couche composant interactive. Construisons-la, en PHP, sans imposer un framework JS."
- **Premier livrable** : `symfony/ux-chartjs`, `symfony/ux-dropzone`, etc. → **bundles wrapper** qui exposent des libs JS via Stimulus.
- **2022** : `symfony/ux-twig-component` puis `symfony/ux-live-component` → **les deux briques pivot** qui font de Symfony UX bien plus qu'un set de wrappers.
- **2023+** : intégration **AssetMapper** (Symfony 6.3) → plus besoin de Node/Webpack pour démarrer.

### Pourquoi ce timing

Trois conditions étaient réunies :

1. **PHP 8 et ses attributs** (`#[AsLiveComponent]`, `#[LiveProp]`) rendent la DX fluide
2. **Stimulus 3** offre un standard JS minimal et stable
3. **L'inspiration croisée** Livewire + LiveView + Hotwire valide le modèle conceptuellement

---

## Slide 4.2 — La vision Symfony UX

### Postulat fondateur

> **"Ramener le modèle composant côté serveur, sans renier PHP ni Twig."**

### Quatre principes directeurs

1. **PHP + Twig restent les langages premiers**
   On n'apprend pas un nouveau DSL, on n'écrit pas de JSX. Une classe PHP, un template Twig — c'est tout.

2. **JS minimal, et seulement où c'est utile**
   Stimulus est utilisé comme **plomberie** discrète. Pas de bundle JS de plusieurs MB, pas de runtime virtuel.

3. **Inspiration explicite des frameworks modernes**
   Composition (React), réactivité server-driven (LiveView, Livewire), navigation progressive (Turbo).

4. **DX 100 % Symfony-native**
   Autowiring, attributs PHP 8, services injectables, Twig, profiler, dump, var-dumper. **Aucun nouvel outillage.**

### Ce qui change pour le développeur Symfony

| Avant | Après |
|-------|-------|
| Logique éparpillée sur 3–4 fichiers | **Un dossier par composant** (PHP + Twig) |
| Props passées en tableau associatif | **Props typées** par propriété PHP |
| État côté serveur ou côté client, jamais les deux cohérents | **État serveur autoritaire**, sync auto |
| Endpoint custom par interaction | **`LiveAction`** déclarative, zéro routing |

---

## Slide 4.3 — Symfony UX en un schéma

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
│     Notify, Autocomplete, LeafletMap, Toggle, Lazy Image…    │
└──────────────────────────────────────────────────────────────┘
```

### Lecture du schéma

- **En haut** : ce qui te concerne quotidiennement (les **deux briques composant**).
- **Au milieu** : la **plomberie JS** (que tu écris peu, voire pas du tout).
- **En bas** : la **chaîne d'assets**, simplifiée par AssetMapper depuis Symfony 6.3.
- **À droite** : les **bundles satellites** — chacun un wrapper d'une lib JS connue, exposé via un Stimulus controller prêt à l'emploi.

### Positionnement clé

- **Bridge** entre backend classique et frontend moderne
- **Progressive enhancement** : adoption brique par brique
- **Opt-in total** : aucune brique n'en impose une autre (Twig Components seuls, Live seuls, Turbo seul…)

---

## Slide 4.4 — Le Stimulus bridge, magie cachée

C'est le **mécanisme central** qui rend Symfony UX agréable : un bundle PHP peut **livrer** des contrôleurs Stimulus, qui s'enregistrent **automatiquement** côté client.

### Comment ça marche (schéma simplifié)

```
1. composer require symfony/ux-dropzone
        │
        ▼
2. Le bundle expose un fichier package.json virtuel + un controller JS
        │
        ▼
3. Symfony Flex écrit dans assets/controllers.json :
   {
     "@symfony/ux-dropzone": {
       "main": { "enabled": true, "fetch": "eager" }
     }
   }
        │
        ▼
4. AssetMapper / Webpack Encore lit ce fichier
        │
        ▼
5. Le controller `dropzone` est disponible dans tous tes templates :
   <div data-controller="dropzone">…</div>
```

### Pourquoi c'est un game-changer

- **Aucun `npm install`** côté projet pour utiliser un bundle UX
- **Activation/désactivation** centralisée dans `assets/controllers.json`
- **Lazy loading** possible (`"fetch": "lazy"`) → JS chargé au premier usage
- **Cohérence** : tous les bundles UX suivent la même convention

### Pour Live Components

C'est exactement par ce mécanisme que **`live_controller.js`** est fourni par `symfony/ux-live-component`. Tu n'écris **jamais** ce JS toi-même.

---

## Slide 4.5 — La famille des bundles UX

L'écosystème UX se découpe en **trois familles** :

### 1. Briques structurelles (le cœur)

| Bundle | Rôle |
|--------|------|
| `ux-twig-component` | Composants statiques (props + template) |
| `ux-live-component` | Composants réactifs (état + actions) |
| `stimulus-bundle` | Pont Symfony ↔ Stimulus |
| `ux-turbo` | Navigation et streams Turbo |

### 2. Wrappers de libs JS populaires

| Bundle | Lib JS sous-jacente | Usage typique |
|--------|--------------------|---------------|
| `ux-chartjs` | Chart.js | Graphiques |
| `ux-dropzone` | Dropzone.js | Upload drag & drop |
| `ux-cropperjs` | Cropper.js | Recadrage d'image |
| `ux-leaflet-map` | Leaflet | Cartes |
| `ux-google-map` | Google Maps | Cartes |
| `ux-react`, `ux-vue`, `ux-svelte` | React/Vue/Svelte | Îlots SPA dans une page Symfony |

### 3. Helpers UI prêts à l'emploi

| Bundle | Apport |
|--------|--------|
| `ux-autocomplete` | Champs avec recherche async (basé sur Tom Select) |
| `ux-notify` | Notifications via Mercure |
| `ux-toggle-password` | Toggle visibilité mot de passe |
| `ux-lazy-image` | Lazy loading d'images avec blurhash |
| `ux-icons` | Iconpack via attributs Twig |

### À retenir

- Tu adoptes **ce dont tu as besoin**, indépendamment du reste.
- La majorité de ces bundles ne sont **pas obligatoires** : ils résolvent un cas concret quand tu le rencontres.
- Le **vrai socle** que ce talk explore reste : **Twig Components + Live Components + Stimulus bridge**.

---

## Slide 4.6 — Comparaison avec les voisins

Pour bien situer Symfony UX, comparons-le aux solutions équivalentes ailleurs :

| Solution | Langage | Modèle | Forces | Limites |
|----------|---------|--------|--------|---------|
| **Symfony UX (Live)** | PHP + Twig | Server-driven, état PHP, DOM diff | DX Symfony native, opt-in | Requête HTTP par interaction réactive |
| **Livewire** | PHP (Laravel) | Server-driven, état PHP, DOM diff | Très mature, écosystème Laravel | Couplé à Laravel |
| **Hotwire / Turbo** | Agnostique (Rails-natif) | HTML-over-the-wire, Turbo Frames/Streams | Ultra-léger, pas de JS spécifique | Pas de modèle composant à proprement parler |
| **HTMX** | Agnostique | Attributs HTML déclencheurs de fragments | Minimaliste, lib JS < 14 KB | Pas d'état côté composant, philosophie différente |
| **Phoenix LiveView** | Elixir | Server-driven via WebSocket | Latence ultra-faible, état serveur | Hors monde PHP |
| **React / Vue (SPA)** | JS (front) | Client-driven, virtual DOM | Riche, écosystème énorme | Double codebase, hydration, complexité |

### Lecture

- Symfony UX **occupe la même case** que Livewire dans l'arborescence philosophique : **server-driven, PHP, opt-in**.
- Il **partage avec Hotwire** l'esprit "HTML-over-the-wire" via Turbo (qui est aussi packagé en `ux-turbo`).
- Il **n'essaie pas** de remplacer React quand on construit un produit type Figma : pour ce cas, on utilise **les wrappers `ux-react`/`ux-vue`** comme îlots.

### La grande question : pourquoi Symfony UX plutôt qu'autre chose ?

- **Tu fais déjà du Symfony** → l'intégration est nulle (même DI, même Twig, même profiler)
- **Ton équipe maîtrise PHP** → pas de double compétence à entretenir
- **Tu veux du SSR par défaut** → SEO, perf perçue, accessibilité
- **Ton interaction reste majoritairement formulaires + tableaux + filtres** → le modèle Live Components est exactement calibré pour ça

---

## Slide 4.7 — Ce que Symfony UX n'est PAS

### Idées reçues à dégager

- ❌ **Pas un SPA framework**
  Tu ne remplaces pas React. Si tu construis un Figma-like, va voir `ux-react` ou un SPA dédié.

- ❌ **Pas un remplaçant de Stimulus**
  Live Components **s'appuie** sur Stimulus. Stimulus garde son rôle pour les comportements purement client (toggle, copy-to-clipboard…).

- ❌ **Pas une solution miracle**
  Chaque interaction Live = une requête HTTP. Pour un canvas 60 fps, ce n'est pas le bon outil.

- ❌ **Pas un nouveau Twig**
  La syntaxe `<twig:...>` reste **du Twig**. Les composants compilent vers Twig classique.

- ❌ **Pas un framework JS déguisé**
  Aucun virtual DOM côté client, aucun runtime à apprendre. Le JS livré est de l'ordre de quelques KB.

### Ce que Symfony UX EST

- ✅ Une **couche composant** pour Twig (Twig Components)
- ✅ Un **modèle réactif server-driven** (Live Components)
- ✅ Un **écosystème de bundles** autour d'un socle Stimulus + AssetMapper
- ✅ Une **initiative communautaire** vivante, sponsorisée par Symfony / SensioLabs

---

## Slide 4.8 — Les trois douleurs du chapitre 2, revisitées

Rappel des trois douleurs identifiées au chapitre 2 :

1. **Pas de contrat de typage** entre la vue et ses données
2. **Pas d'unité d'organisation** naturelle
3. **Pas de réactivité server-driven**

### La réponse Symfony UX, en deux temps

| Douleur | Brique de réponse |
|---------|-------------------|
| Contrat de typage | **Twig Components** → `#[ExposeInTemplate]`, props PHP typées |
| Unité d'organisation | **Twig Components** → 1 classe + 1 template, colocalisés |
| Réactivité server-driven | **Live Components** → `#[LiveProp]`, `#[LiveAction]`, DOM morphing |

### Lecture

Symfony UX **n'invente pas** un modèle radicalement nouveau. Il **assemble proprement** ce que la communauté front a validé depuis 10 ans, et le **traduit en idiomes Symfony**. C'est ce qui fait sa force : tu ne désapprends rien, tu **gagnes** une couche manquante.

---

## 💬 Message clé

> **"Symfony UX = bridge backend ↔ frontend."**
> On garde la productivité Symfony, on gagne le modèle composant, on évite la complexité SPA quand elle n'est pas nécessaire.

### Trois mots à retenir

- **Bridge** : entre l'écosystème PHP/Twig et les patterns front modernes
- **Opt-in** : adoption progressive, brique par brique
- **Server-driven** : la vérité reste côté serveur, le client n'est qu'une projection

---

## 🗣️ Narration (script oral)

> "L'approche de Symfony UX est pragmatique : plutôt que de réinventer la roue, ils se sont posé la question 'qu'est-ce qui marche dans les frameworks modernes ?' et ont porté ces idées en PHP. Le résultat, c'est une initiative — pas un framework — qui regroupe une collection de bundles autour d'un socle commun.
>
> Ce socle, c'est trois choses : un **Stimulus bridge** qui permet à un bundle PHP de livrer du JS sans que tu touches à npm ; **Twig Components** pour la composition statique avec des props typées ; et **Live Components** pour l'interactivité server-driven. Les trois sont indépendants, tu adoptes ce dont tu as besoin.
>
> Ce qui rend la promesse crédible, c'est que tout ça est **100 % Symfony-native** : autowiring, attributs PHP 8, profiler, services injectables. Tu n'apprends pas un nouvel outillage, tu **étends** celui que tu utilises déjà.
>
> Et la grande différence avec les SPAs : la vérité reste **côté serveur**. Pas de double codebase, pas de synchronisation d'état hasardeuse. Le client n'est qu'une projection de l'état PHP. C'est exactement ce qu'on cherchait depuis le chapitre 2."

---

## 🧭 Transition vers le chapitre 5

On a la vision. On a la cartographie. On a le positionnement.

Attaquons maintenant la **première brique** concrète : **Twig Components**, le socle qui rend tout l'édifice possible. C'est lui qui apporte la **classe PHP + template Twig + props typées** — la fondation sur laquelle Live Components viendra ajouter l'état et la réactivité.
