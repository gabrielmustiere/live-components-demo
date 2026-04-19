# ⚖️ 8. Comparaison — Twig Component vs Live Component vs React/Vue

## 🎯 Objectif du chapitre

Positionner **clairement** les trois approches pour savoir **quand choisir quoi** — sans dogmatisme, sans effet de mode, et sans tomber dans le "tout front" ni le "tout serveur".

À la fin du chapitre, on doit pouvoir répondre à ces questions :

1. **Qu'est-ce qui différencie concrètement** un Twig Component, un Live Component et un composant React/Vue, au-delà du langage ?
2. **Quels critères objectifs** utiliser pour arbitrer entre les trois sur une fonctionnalité donnée ?
3. **Peut-on (doit-on ?) mélanger** les trois dans une même application ?
4. **Où est la frontière** entre "latence acceptable" et "il faut passer client" ?

> ⚠️ **À retenir** : ce n'est **pas** une compétition. Les trois approches visent des zones de responsabilité différentes. Le vrai enjeu, c'est de **placer la frontière au bon endroit** — et cette frontière varie d'un projet à l'autre.

---

## 🧭 Slide 8.1 — La grille d'évaluation

Avant de trancher, on fixe les critères. Une comparaison n'est utile que si elle est faite **sur des axes explicites**. On en retient neuf, qui couvrent à la fois la DX, la perf, la gouvernance et le rapport au web.

| Axe | Question posée |
|-----|----------------|
| **Lieu du rendu** | Où le HTML est-il produit ? (serveur, client, mixte) |
| **Lieu de l'état** | Où vit la source de vérité ? (serveur, client, les deux synchronisés) |
| **Latence perçue** | Quel est le délai entre action utilisateur et feedback visuel ? |
| **Dépendance réseau** | L'UI fonctionne-t-elle hors-ligne ? en réseau dégradé ? |
| **Duplication logique** | Doit-on réécrire la même règle métier en PHP et en JS ? |
| **Pipeline de build** | Faut-il un bundler (Vite, Webpack, esbuild) ? |
| **Courbe d'apprentissage** | Combien de temps pour être productif dans un projet Symfony classique ? |
| **SEO / accessibilité** | Le contenu est-il indexable et lisible sans JS au premier paint ? |
| **Testabilité** | Peut-on tester la logique d'UI avec l'outillage standard du backend ? |

On utilise cette grille dans toute la suite du chapitre.

---

## Slide 8.2 — Tableau comparatif général

| Feature                     | Twig Component                | Live Component                     | React / Vue                        |
|-----------------------------|-------------------------------|------------------------------------|------------------------------------|
| **Lieu du rendu**           | Serveur uniquement            | Serveur + patch Ajax (morphdom)    | Client (SSR optionnel)             |
| **Lieu de l'état**          | Pas d'état (rendu figé)       | Serveur, sérialisé dans le DOM     | Client, en mémoire JS              |
| **JS requis**               | ❌ Aucun                       | ⚠️ Stimulus inclus (aucun code à écrire) | ✅ Oui, significatif                |
| **Pipeline de build**       | ❌ (AssetMapper suffit)        | ❌ (AssetMapper suffit)             | ✅ Vite / Webpack / esbuild         |
| **Poids JS côté client**    | ~0 Ko                         | ~25–30 Ko (Stimulus + live)        | 40–150 Ko+ selon la stack          |
| **SEO natif**               | ✅ HTML complet au 1er paint   | ✅ HTML complet au 1er paint        | ⚠️ SSR ou prerender requis         |
| **Fonctionnement offline**  | ❌                             | ❌                                  | ✅ (service workers, PWA)           |
| **Latence interaction**     | N/A (pas d'interaction)       | Réseau (50–300 ms typique)         | Locale (instantanée)               |
| **Duplication PHP/JS**      | Aucune                        | Aucune                             | Fréquente (validation, formatage)  |
| **Courbe d'apprentissage**  | Faible (Twig enrichi)         | Moyenne (quelques conventions)     | Élevée (écosystème entier)         |
| **Testabilité**             | PHPUnit, test visuel           | PHPUnit + test d'actions Live       | Jest/Vitest + testing-library      |
| **Accessibilité**           | Natif (HTML pur)              | Natif (DOM morphing préserve focus) | Dépend de la discipline équipe     |
| **Écosystème / support**    | Twig / Symfony / UX bundle    | Symfony UX / Stimulus              | NPM (énorme, fragmenté)            |

**Lecture** : aucune ligne ne "gagne" sur tous les axes. Chaque approche fait un **choix d'ingénierie** — et ce choix ferme certaines portes pour en ouvrir d'autres.

---

## Slide 8.3 — Axe par axe, les nuances qui comptent

### 🖼️ Rendu & état

- **Twig Component** rend **une fois**, sur le serveur, et n'a pas de mémoire. C'est le modèle d'un "fragment HTML typé". Son état est celui de ses props à l'instant du rendu — rien de plus.
- **Live Component** rend côté serveur à chaque cycle, **mais transporte son état dans le DOM** (`data-live-props-value`). L'état vit sur le serveur le temps de l'action, puis repart dans le HTML. Stateless entre deux requêtes, persistant à l'échelle de la session utilisateur.
- **React / Vue** tiennent l'état en mémoire JS côté client. C'est la force (réactivité instantanée) et la faiblesse (perdu au refresh si pas persisté).

### ⚡ Latence & ressenti

- **Twig Component** : pas d'interaction → la question ne se pose pas.
- **Live Component** : chaque action = un round-trip HTTP. Sur LAN/fibre, c'est 30–80 ms — invisible. Sur 4G dégradée ou wifi hôtel, ça devient perceptible. Le `debounce` et le `loading` indicator font une grande partie du boulot pour atténuer ça.
- **React / Vue** : feedback instantané. C'est **la** raison de sortir le client quand on fait du canvas, du drag & drop, ou du temps réel multi-utilisateurs.

### 🔁 Duplication logique

C'est souvent **le critère décisif** pour une équipe Symfony :

- Twig/Live → **une seule source de vérité**, PHP. La validation écrite dans l'entité Doctrine est rejouée automatiquement.
- React/Vue → on finit presque toujours par **réécrire la validation** côté client pour l'UX, puis on prie pour que les deux restent synchrones. Les équipes qui maintiennent ça sur 3 ans savent le prix.

### 🛠️ Outillage & DX

- Twig/Live → `composer require`, un attribut PHP, un template. Pas de `node_modules`, pas de `vite.config.js`, pas de CI JS.
- React/Vue → pipeline complet, avec ses propres cycles d'upgrade, ses dépendances en cascade, et son propre écosystème de types (TypeScript, souvent non négociable).

### 🌍 SEO & accessibilité

- Twig/Live → HTML complet dès la réponse initiale. Les crawlers sont contents par défaut, les screen readers aussi.
- React/Vue → sans SSR, le premier paint est souvent une coquille vide. SSR rajoute de la complexité (hydration, mismatch, double rendu). C'est un vrai sujet, pas un détail.

### 🧪 Testabilité

- Live Component se teste **comme une classe PHP** : on instancie, on appelle la méthode, on assert. Pas de DOM, pas de headless browser, pas de `jsdom`.
- React/Vue demandent tout l'outillage front (Vitest, testing-library, setup de DOM, mocks de routing, etc.).

---

## Slide 8.4 — Où brille chaque approche (avec exemples réels)

### 🧱 Twig Component brille quand…

- L'UI est **statique ou rendue au chargement**, sans interaction complexe.
- On construit un **design system interne** : boutons, cards, alerts, badges, breadcrumbs, avatars.
- On veut **factoriser du markup** sans importer de complexité.
- Le rendu dépend de **données serveur** (BDD, config, user connecté, droits).

**Exemples d'apps typiques** : dashboards read-only, portails d'information, pages marketing dynamiques, back-offices CRUD où les interactions passent par des formulaires Symfony classiques.

### ⚡ Live Component brille quand…

- L'UI a besoin d'**interactivité modérée** : search-as-you-type, filtres dynamiques, forms multi-étapes, quick-edit inline, panier.
- On veut **zéro duplication** de logique métier front/back.
- La **latence réseau est acceptable** — c'est-à-dire sur des interactions où 100 ms de délai sont invisibles (recherche, filtre, submit).
- On reste dans **l'écosystème Symfony** sans monter un front séparé.

**Exemples d'apps typiques** : backoffices e-commerce, CRM internes, outils métier de PME, SaaS B2B avec UX riche mais pas "temps réel", apps Intranet.

### ⚛️ React / Vue brillent quand…

- L'UI est **très interactive** au sens client : drag & drop fluide, canvas, éditeurs visuels, diagramme interactif, whiteboard collaboratif.
- On a besoin d'**état client riche** : undo/redo, optimistic UI, animations complexes coordonnées, sélection multiple avec clavier.
- L'app doit fonctionner **offline** ou être packagée **mobile native** (Capacitor, React Native).
- L'équipe a une **expertise front** déjà forte et un pipeline JS rôdé.

**Exemples d'apps typiques** : éditeurs graphiques (Figma-like), tableaux blancs collaboratifs, IDE en ligne, apps mobiles, dashboards avec visualisations temps réel.

---

## Slide 8.5 — Matrice de décision

Deux axes : **où vit l'état** (serveur ↔ client) et **niveau d'interactivité** (faible ↔ élevé).

```
                     Interactivité élevée
                            ▲
                            │
                            │     ╭──────────────╮
                            │     │   React /    │
                            │     │    Vue       │
                            │     │              │
                            │     │ canvas,      │
                            │     │ drag & drop, │
                 ╭──────────┼─────┤ offline,     │
                 │   Live   │     │ temps réel   │
                 │ Component│     ╰──────────────╯
                 │          │
                 │ filtres, │
                 │ search,  │
                 │ wizards, │
                 │ panier   │
   Serveur ──────┼──────────┼──────────────────────► Client
   (source       │          │
    de vérité)   │          │
                 │   Twig   │     ╭──────────────╮
                 │ Component│     │   (cas rare) │
                 │          │     │   SPA sans   │
                 │ layout,  │     │   state      │
                 │ cards,   │     │              │
                 │ design   │     ╰──────────────╯
                 │ system   │
                 ╰──────────┤
                            │
                            ▼
                     Interactivité faible
```

**Règle pratique** : partir d'en bas à gauche (Twig). Monter vers Live dès qu'une interaction serveur-dépendante apparaît. Basculer à droite uniquement si la latence devient un problème UX **mesurable**, pas fantasmé.

---

## Slide 8.6 — L'hybridation, le vrai modèle

Le meilleur projet Symfony moderne n'est **pas 100 % Twig**, ni **100 % Live**, ni **100 % React**. C'est une app qui **place la bonne brique au bon endroit**.

### Anatomie d'une app réaliste

Imaginons un back-office e-commerce :

| Zone                            | Brique choisie        | Pourquoi |
|---------------------------------|-----------------------|----------|
| Layout, header, sidebar, footer | **Twig Component**    | Statique, partagé partout, zéro interactivité |
| Design system (buttons, cards)  | **Twig Component**    | Réutilisabilité, cohérence visuelle |
| Liste produits filtrable        | **Live Component**    | Filtres, pagination, search — logique serveur |
| Formulaire commande (wizard)    | **Live Component**    | Champs conditionnels, validation métier live |
| Autocomplete client/produit     | **Live Component**    | Debounce, recherche serveur, zéro JS custom |
| Éditeur de fiche produit riche  | **React** (zone iso) | Drag & drop images, éditeur WYSIWYG, undo/redo |
| Tableau de bord analytics       | **Twig + Chart.js**   | Rendu serveur + sprinkle JS, pas besoin de SPA |

**Une seule stack** (Symfony + Twig), **deux moteurs de réactivité** (Live pour le serveur-driven, React isolé pour les zones qui l'exigent vraiment), **un design system** unifié au niveau Twig.

### Le principe

> **Les trois approches ne sont pas des religions mutuellement exclusives. Ce sont trois outils dans la même boîte.**

Ce qui change avec Symfony UX, c'est que **le défaut a bougé**. Avant, on prenait React par réflexe parce que c'était "le moderne". Maintenant, on part de Twig/Live, et on va chercher React **uniquement** sur les zones qui justifient son coût.

---

## Slide 8.7 — Et les cousins : Livewire, Hotwire, HTMX

Live Components n'est pas sorti de nulle part. Il appartient à une **famille de solutions server-driven** qui a émergé en parallèle dans chaque écosystème.

| Techno             | Écosystème | Modèle                               |
|--------------------|------------|--------------------------------------|
| **Live Components**| Symfony    | Classe PHP + Twig + Ajax + morphdom  |
| **Livewire**       | Laravel    | Classe PHP + Blade + Ajax + morphdom |
| **Hotwire / Turbo**| Rails      | Streams + Frames + morphing HTML     |
| **HTMX**           | Agnostique | Attributs HTML → requêtes serveur    |
| **Phoenix LiveView**| Elixir    | WebSocket + diff DOM côté serveur    |

**Ce que ça dit** : l'idée "pas besoin de SPA pour faire de l'UI moderne" n'est pas un caprice Symfony. C'est un **mouvement de fond** de l'industrie, porté par des équipes qui ont payé le prix du "tout React" et qui cherchent un meilleur compromis.

Live Components est **la version Symfony native** de cette philosophie — avec l'avantage d'être intégré à un framework déjà complet (DI, ORM, validation, forms, sécurité).

---

## Slide 8.8 — Les pièges à éviter

### ❌ "On met React partout par réflexe"

- Pour un backoffice CRUD, c'est **overkill** : 80 % du code front est du formulaire et du listing.
- **Duplication** logique back/front : on réécrit la validation, le formatage, les règles métier.
- **Dette technique future** : le pipeline JS vieillit plus vite que le code PHP.
- Un nouvel arrivant dans l'équipe doit maîtriser **deux stacks** pour contribuer sur une feature.

### ❌ "On met Live Component partout"

- Pour une UI très interactive (chat temps réel, canvas, éditeur collaboratif), la latence devient **visible et irritante**.
- Le serveur devient un **goulot d'étranglement** si chaque frappe clavier déclenche un round-trip.
- Certaines mécaniques (undo/redo complexe, sélection multi-objets avec clavier) sont **inadaptées** au modèle server-driven.

### ❌ "On reste sur Twig include par habitude"

- On passe à côté de la **réutilisabilité typée** des Twig Components.
- On ne gagne rien sur la **maintenabilité** : le markup reste éparpillé, sans contrat.
- On empile de petits bricolages Stimulus dès qu'il faut la moindre interactivité.

### ❌ "On choisit par techno, pas par besoin"

- Le vrai raisonnement : "quelle est la **nature de cette interaction** ?"
- Pas : "on est une équipe Symfony, donc on fait tout en Live Component".
- Ni : "on est une équipe front, donc tout en React".

Laisser la nature du besoin piloter le choix technique, pas l'inverse.

---

## 💬 Message clé

> **Twig Component, Live Component et SPA ne sont pas concurrents — ils sont complémentaires.**
> Un même projet peut (et doit souvent) combiner les trois selon les zones. La compétence-clé d'un lead dev, ce n'est pas de choisir **une** stack : c'est de savoir **placer la frontière**.

---

## 🗣️ Narration (script oral)

> "Ce qu'il faut comprendre, c'est que Symfony UX ne dit pas 'n'utilisez plus jamais React'. Il dit : 'pour 80 % de vos besoins, vous n'en avez pas besoin'. La vraie compétence d'un lead dev, c'est de savoir **où placer la frontière** : un Twig Component pour le design system, du Live Component pour les formulaires dynamiques et les filtres, et du React **uniquement** pour les zones où la latence ou l'état client l'exigent vraiment — un éditeur WYSIWYG riche, un canvas, une app qui doit fonctionner offline.
>
> Le changement philosophique est là : **le défaut a bougé**. Avant Symfony UX, on prenait React par réflexe. Maintenant, on part du serveur et on ne va chercher le client que quand il se justifie. C'est pas un retour en arrière, c'est une remise à plat. Et franchement, sur un backoffice bien conçu en Live Components, la productivité est sans commune mesure avec ce qu'on faisait en SPA il y a deux ans."

---

## 🧭 Transition vers le chapitre 9

On a vu **le cadre théorique** : trois approches, neuf critères, une matrice de décision. Place au concret — **quels cas d'usage réels** pour chaque brique, avec du code qu'on peut copier-coller demain matin en prod ?
