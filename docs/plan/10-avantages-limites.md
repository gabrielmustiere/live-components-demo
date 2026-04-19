# 🧠 10. Avantages & limites

## 🎯 Objectif du chapitre

Avoir une vision **lucide** des forces et des faiblesses de Symfony UX. Un lead dev doit pouvoir **défendre la solution** devant un comité d'architecture **et la critiquer** devant un sponsor qui rêve de React partout.

On répond concrètement à :

- Qu'est-ce que Symfony UX nous fait **gagner**, au-delà du ressenti ?
- Où est-ce qu'il **coince**, et à quel point ?
- Quels sont les **anti-patterns** qui transforment la bonne idée en cauchemar ?
- Comment **atténuer** les limites quand on y est confronté ?
- À quel moment la **bonne réponse** est "ne prenez pas Live Components ici" ?

> ⚠️ **À retenir** : aucun outil n'est universellement bon ou mauvais — c'est **l'adéquation au problème** qui compte. Ce chapitre cherche la vérité nuancée, pas un plaidoyer.

---

## Slide 10.1 — Les avantages

### 🟢 DX Symfony excellente

- **Autowiring**, **attributs PHP 8**, **services**, **events**, **profiler** : tout ce qu'on connaît déjà
- Un Live Component est **une classe PHP comme les autres** — pas une DSL parallèle
- Debug avec **Xdebug**, **breakpoint** dans une `LiveAction`, `dump()` sur une prop : le workflow ne change pas
- Les **logs Monolog** capturent les Live requests exactement comme les requêtes classiques

```php
#[AsLiveComponent]
final class OrderEditor
{
    use DefaultActionTrait;

    public function __construct(
        private OrderRepository $orders,
        private WorkflowInterface $orderWorkflow,
        private LoggerInterface $logger,
    ) {}

    // DI classique, logger classique, workflow classique.
    // Rien de nouveau à apprendre côté Symfony.
}
```

### 🟢 Pas de JS nécessaire (ou presque)

- Pour **~90 % des cas**, zéro ligne de JS écrite à la main
- Stimulus est fourni mais **invisible** pour la plupart des usages (pas besoin de `static targets`, de listeners, de fetch)
- Pas de **build frontend lourd** (webpack, vite) à maintenir côté logique métier — AssetMapper suffit
- Plus de **duplication** de la validation métier côté JS (les règles vivent côté PHP et sont exécutées sur chaque round-trip)

### 🟢 SEO natif

- Rendu **server-side par défaut** : le HTML complet part dans la première réponse
- Pas de problème d'indexation comme sur une SPA mal configurée (Googlebot exécute du JS, mais pas toujours bien, et jamais rapidement)
- Compatibilité immédiate avec **meta tags dynamiques**, **Open Graph**, **Schema.org**

### 🟢 Maintenable sur la durée

- Un développeur back peut **lire, écrire et maintenir** un Live Component sans changer de contexte mental
- Les tests se font avec **PHPUnit** (voir chapitre 7 sur `InteractsWithLiveComponents`), comme le reste
- Une seule **source de vérité** : l'état vit côté serveur, pas besoin de synchroniser un store Redux
- **Refactor sécurisé** : PHPStan niveau 9 couvre les LiveProp et les LiveAction comme n'importe quelle méthode

### 🟢 Adoption progressive

- On peut **introduire Twig Components** sans rien changer d'autre — zéro réécriture
- Puis **ajouter Live** quand l'interactivité le demande, composant par composant
- Aucune migration big-bang : on garde le code existant, on refactore au fil des tickets
- **Pas de bascule** framework-to-framework : c'est **toujours du Symfony**

### 🟢 Une seule équipe, end-to-end

- Pas besoin d'une **équipe front séparée** pour la plupart des features
- Les devs Symfony livrent **une feature complète** : formulaire dynamique, validation, persistence, rendu
- **Moins de synchronisation** inter-équipes → cycles de livraison plus courts
- Recrutement ciblé sur **un profil unique** (Symfony full-stack) plutôt que deux

### 🟢 Répond aux trois douleurs du chapitre 2

Rappel des **trois douleurs historiques** identifiées au chapitre 2 :

| Douleur | Réponse Symfony UX |
|--------|--------------------|
| Pas de **contrat de typage** entre vue et données | Props typées PHP 8, autocomplétion IDE, PHPStan |
| Pas d'**unité d'organisation** (logique/markup/comportement éparpillés) | Un composant = 1 classe + 1 template, côte à côte |
| Pas de **réactivité server-driven** sans sortir de Symfony | Live Components + DOM morphing |

---

## Slide 10.2 — Les limites

### 🔴 Latence serveur

Chaque interaction utilisateur déclenche un **round-trip HTTP**. Ordre de grandeur :

| Environnement | Latence ~ | Perception |
|---------------|-----------|------------|
| Dev local | 30–80 ms | Imperceptible |
| Prod, même région | 80–200 ms | Acceptable |
| Prod, mobile 4G | 200–500 ms | Visible mais OK si non-bloquant |
| Prod, mobile 3G / réseau dégradé | 500 ms–2 s | Frustrant |

**Où ça fait mal** :

- Interactions **très fréquentes** (typing sans debounce, drag & drop, sliders)
- UI à **60 fps attendues** (canvas, animations complexes, éditeurs)
- Utilisateurs en **mobilité** avec connexion instable

**Ce n'est pas adapté** à : jeux navigateur, éditeurs temps réel collaboratifs (Google Docs, Figma), outils de dessin vectoriel, dashboards avec rafraîchissement sub-seconde.

### 🔴 Couplage backend & scalabilité

- Plus d'interactions = **plus de requêtes** à absorber sur vos endpoints Symfony
- Un utilisateur qui tape dans un champ avec debounce = **2 à 5 requêtes / seconde** pendant la saisie
- **Charge multipliée** par rapport à une SPA classique où le typing reste côté client
- Chaque Live request rejoue : **firewall, security voters, Doctrine bootstrap, rendu Twig**
- Le **profiling** devient critique : un N+1 qui passait inaperçu en rendu page devient visible

**Traduction capacitaire** : si votre app prend 100 req/s en rendu classique, prévoyez **300–500 req/s** avec Live Components actifs.

### 🔴 Moins flexible qu'un vrai SPA

- **Pas d'état client riche** : pas d'undo/redo local, pas de draft autosave offline
- **Optimistic UI** plus difficile (possible, mais demande du JS custom)
- **Offline** : impossible par nature (chaque interaction a besoin du serveur)
- **Animations complexes** : pas d'équivalent natif à Framer Motion, GSAP, React Spring
- **Drag & drop** avancé : possible mais moins fluide qu'une solution 100 % client

### 🔴 Courbe d'apprentissage spécifique

- **Hydratation / déshydratation** des LiveProp : les objets ne voyagent pas, il faut **typer** et parfois `hydrateWith` / `dehydrateWith`
- **Cycle de vie** (voir chapitre 7) : quand un re-render ne se déclenche pas, le debug peut être subtil
- **`LiveListener`** entre composants parent/enfant : concept nouveau à assimiler
- **Stimulus** reste à comprendre pour les cas avancés (focus management, intégrations JS tiers)
- Temps d'apprentissage réaliste pour un dev Symfony expérimenté : **1 à 2 semaines** pour être autonome

### 🔴 Écosystème plus restreint

- Pas la richesse de **React** (des millions de composants NPM, dont 95 % sont du bruit)
- Moins de **copy-paste Stack Overflow** disponible
- Certains patterns (**virtual scrolling**, **infinite list** ultra-optimisée, **charts interactifs**) demandent plus de travail
- Documentation officielle **excellente** mais communauté plus petite → moins de tutoriels tiers, moins de vidéos YouTube

---

## Slide 10.3 — Tableau de synthèse

| Critère | Avantage | Limite |
|---------|----------|--------|
| **Productivité dev** | Très élevée (1 classe + 1 template) | Courbe d'apprentissage hydratation / cycle de vie |
| **Performance interaction** | Bonne en LAN / 4G rapide | Dégradée en 3G / réseau instable |
| **Performance serveur** | Scalabilité horizontale stateless | Volume de requêtes x3 à x5 vs app classique |
| **SEO** | Natif, rien à configurer | — |
| **Offline** | — | Impossible par nature |
| **Animations / UI ultra-fluides** | OK pour 95 % des cas | Limité pour les 5 % restants (canvas, 60 fps) |
| **Écosystème** | Symfony complet (DI, tests, profiler) | Bibliothèque de composants moins fournie que React |
| **Équipe** | Mono-équipe back end-to-end | Moins attractif pour des front specialists |
| **Recrutement** | Profil Symfony classique | Pas de "profil Live Components" dédié sur le marché |

---

## Slide 10.4 — Anti-patterns à connaître

Les limites deviennent critiques quand on utilise Live Components **là où il ne faut pas**. Cinq pièges qu'on voit en vrai.

### ❌ Trop d'état dans une LiveProp

```php
// À NE PAS FAIRE
#[LiveProp(writable: true)]
public Order $order; // Entité Doctrine complète sérialisée à chaque request
```

**Problème** : hydratation lourde, risque d'exposer des champs sensibles, payload réseau gonflé.

**Mieux** :

```php
#[LiveProp]
public int $orderId;

public function getOrder(): Order
{
    return $this->orders->find($this->orderId);
}
```

### ❌ Chaînes de LiveAction qui multiplient les round-trips

```twig
{# À NE PAS FAIRE — trois requêtes pour une seule action utilisateur #}
<button data-action="live#action" data-live-action-param="addItem">Ajouter</button>
<button data-action="live#action" data-live-action-param="recalcTotal">Total</button>
<button data-action="live#action" data-live-action-param="saveCart">Sauver</button>
```

**Mieux** : une seule action `checkout` qui fait les trois étapes côté serveur, ou groupage `action1|action2`.

### ❌ Live Component pour du purement statique

Si ton composant n'a **ni LiveProp writable, ni LiveAction**, c'est un **Twig Component**. Tu payes le coût de l'hydratation pour rien.

### ❌ Forcer Live là où la latence tue l'UX

Exemples concrets à ne **pas** faire en Live :

- Un **slider** couleur qui met à jour une preview en live → restez sur du Stimulus pur
- Un **éditeur de texte riche** (WYSIWYG) → bibliothèque dédiée (TipTap, ProseMirror)
- Un **canvas de dessin** → JS natif ou librairie client
- Une **carte Leaflet interactive** avec milliers de markers → client-side

### ❌ Ignorer le profiling

Sur une page avec 5 Live Components, chaque interaction peut déclencher **plusieurs requêtes en cascade** si les composants sont imbriqués. Sans profiler ouvert, tu ne le vois pas.

---

## Slide 10.5 — Comment atténuer les limites

### Pour la latence

**Debounce** sur les inputs fréquents :

```twig
<input data-model="debounce(300)|query" type="search">
```

**Batching d'actions** côté client :

```twig
<button data-action="live#action" data-live-action-param="validate|save|redirect">
    Enregistrer
</button>
```

**LiveProp `url: true`** pour les filtres : l'état va dans l'URL, back/forward marchent, le composant n'a pas besoin de persister côté serveur.

**LiveProp client-only** (`writable: true` sans `onUpdated`) : l'état change côté client sans re-render serveur obligatoire pour chaque frappe.

### Pour la scalabilité

- **HTTP cache** sur les Live endpoints cacheables (rare mais possible pour certains widgets publics)
- **Blackfire** ou le **Symfony profiler** en continu pour identifier les Live actions lentes
- **Horizontal scaling** classique : les composants restent **stateless côté serveur** entre deux requêtes (l'état voyage dans la request, signé)
- **Doctrine second-level cache** pour les entités lues en boucle

### Pour l'écosystème

- Construire son propre **design system Twig** (investissement qui paie sur la durée)
- Mixer avec des **composants front** (React, Vue, lit) **uniquement là où c'est justifié** — Stimulus + Symfony UX ne l'interdisent pas
- S'appuyer sur **[ux.symfony.com](https://ux.symfony.com/)** pour les bundles officiels (Autocomplete, Chartjs, Dropzone, Cropperjs, Typed, Map…)

### Pour la courbe d'apprentissage

- Commencer par des **Twig Components** seuls, sans Live
- Faire un **premier Live Component trivial** (compteur, toggle) pour sentir le cycle
- Lire activement les logs du profiler pour chaque re-render les premières semaines

---

## Slide 10.6 — Grille de décision rapide

Pour décider si Live Components est un bon fit sur une fonctionnalité donnée :

| Question | Réponse = oui → | Réponse = non → |
|----------|-----------------|-----------------|
| L'interaction dépend-elle d'**état serveur** (DB, session) ? | ✅ Live Component | Twig Component ou Stimulus |
| Est-ce que **< 1 interaction / seconde** suffit ? | ✅ Live Component | Stimulus client-only |
| L'utilisateur peut-il tolérer une latence de **~200 ms** ? | ✅ Live Component | SPA ou client natif |
| La feature est-elle utilisée **offline** ? | SPA / PWA | ✅ Live Component |
| L'animation demande-t-elle du **60 fps** ? | Client JS / canvas | ✅ Live Component |
| Le rendu doit-il être **indexé par Google** ? | ✅ Live Component | SPA avec SSR nécessaire |

**Règle d'or** : si les réponses pointent majoritairement vers Live Component, c'est un bon fit. Dès qu'une réponse critique bascule, il faut une **autre brique**.

---

## 💬 Message clé

> **"Symfony UX n'est pas une solution miracle — c'est un outil avec un domaine d'application clair."**
>
> Bien utilisé (CRUD, backoffice, SaaS B2B, e-commerce, intranet), il **simplifie énormément**. Mal utilisé (UI ultra-dynamiques, offline, animations 60 fps), il **frustre**.

---

## 🗣️ Narration (script oral)

> "Soyons honnêtes : Symfony UX ne remplace pas tout. Si on vous demande de construire un Figma, un Google Docs ou un jeu dans le navigateur, restez sur React, Vue ou du natif. Mais pour **tout ce qui est CRUD, backoffice, e-commerce, SaaS B2B, intranet** — autrement dit l'écrasante majorité des applications web qu'on écrit dans la vraie vie — Symfony UX couvre les besoins avec une productivité qu'on n'avait pas avant.
>
> Le vrai piège, ce n'est pas l'outil lui-même, c'est de vouloir **le forcer** là où il n'a pas sa place. Un éditeur de dessin en Live Component, c'est une catastrophe garantie. Un formulaire multi-étapes en Live Component, c'est trente lignes de PHP et une expérience utilisateur irréprochable. La différence, c'est **savoir lire le problème** avant de dégainer la solution."

---

## 🧭 Transition vers le chapitre 11

Assez parlé théorie et tableaux — **passons au code qui bouge à l'écran**. Deux démos courtes pour rendre tout ça tangible.
