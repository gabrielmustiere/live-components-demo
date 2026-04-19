---
name: enrichir-chapitre-plan
description: Enrichit un chapitre du dossier docs/plan/ de la présentation Symfony UX. À déclencher dès que l'utilisateur demande d'étoffer, enrichir, développer, compléter, retravailler ou approfondir un chapitre du plan (ex. "enrichis le chapitre 5", "développe docs/plan/07-cycle-de-vie.md", "étoffe le chapitre sur Twig Components", "complète la doc du chapitre 9"). Le skill produit un fichier markdown structuré, pédagogique, en français, calibré pour servir de support à un talk technique. Il peut s'appuyer sur la documentation officielle Symfony (Twig Components, Live Components, Stimulus, Turbo, AssetMapper) via WebFetch.
---

# Enrichir un chapitre du plan de présentation

## Contexte

Le dossier `docs/plan/` contient les **chapitres d'un talk** sur Symfony UX et Live Components. Chaque chapitre est un fichier markdown qui sert de **support narratif** pour des slides. Certains chapitres sont déjà enrichis (02, 04 servent de référence de style), d'autres sont encore à l'état de squelette.

L'objectif du skill : transformer un chapitre léger en **support pédagogique dense, concret, prêt à dérouler**, dans le **même style éditorial** que les chapitres déjà enrichis — sans imposer une structure rigide, parce que tous les chapitres n'ont pas la même nature (théorique, technique, démo, conclusion).

## Quand utiliser ce skill

Dès que l'utilisateur :
- demande d'enrichir, étoffer, développer, compléter, approfondir, retravailler un chapitre dans `docs/plan/`
- pointe un fichier `docs/plan/NN-...md` à améliorer
- évoque la préparation des slides du chapitre N

Ne pas utiliser pour : créer un chapitre depuis zéro (sauf demande explicite), modifier la structure globale du plan, écrire les slides finales (Reveal.js / etc.).

## Étapes

### 1. Cartographier le chapitre cible

Lire :
- Le **chapitre cible** (état actuel, intentions, message clé déjà esquissé)
- Le chapitre **précédent** (n-1) et **suivant** (n+1) si disponibles → continuité narrative, transitions cohérentes
- `docs/plan/02-avant-symfony-ux.md` ou `docs/plan/04-symfony-ux-vision.md` comme **étalons de style** (vocabulaire, ton, densité)

À ce stade, identifier :
- **La nature du chapitre** (voir typologie ci-dessous) — c'est ce qui dicte la structure d'enrichissement
- **Le message clé** que le chapitre doit faire passer
- **Les hooks** vers le chapitre suivant
- **Ce qui manque** : exemples concrets ? Tableaux comparatifs ? Code ? Diagrammes ASCII ? Mise en perspective historique ? Anti-patterns ?

### 2. Choisir la structure adaptée à la nature du chapitre

Les chapitres n'ont pas tous la même fonction narrative. Adapter la structure plutôt que copier-coller le moule de 02/04. Indications :

| Type de chapitre | Ingrédients utiles |
|------------------|--------------------|
| **Introduction / pose du problème** (01) | Hook, contexte historique, plan du talk, à qui ça s'adresse |
| **Inventaire / archéologie** (02) | Slide par option, "ce que ça résout / pas", grille comparative finale |
| **Concepts venus d'ailleurs** (03) | Définitions, généalogie, parallèles cross-frameworks |
| **Vision / positionnement** (04, 12) | Postulat, schéma d'architecture, comparaison avec voisins, ce que c'est / pas |
| **Brique technique** (05, 06) | Anatomie (fichiers, attributs PHP), code minimal annoté, tour des features, gotchas |
| **Mécanique interne** (07 cycle de vie) | Diagrammes de séquence ASCII, étapes numérotées, ce qui se passe côté serveur vs client |
| **Comparaison** (08) | Tableaux multi-axes, critères explicites, verdict nuancé |
| **Cas d'usage** (09) | Recettes concrètes, code idiomatique, "quand utiliser / quand éviter" |
| **Avantages / limites** (10) | Liste équilibrée, anti-patterns, pièges réels avec exemples |
| **Démo live** (11) | Script pas-à-pas, "ce qu'on montre / ce qu'on dit", moments de surprise, plan B en cas de bug |
| **Positionnement stratégique** (12) | Arbre de décision, matrice (complexité × interactivité), conseils par contexte |
| **Conclusion** (13) | Récap des messages clés, take-aways, ressources, ouverture |

Cette typologie est indicative — composer librement selon le besoin réel du chapitre.

### 3. Ingrédients communs (à utiliser quand pertinent)

Quel que soit le type de chapitre, ces blocs sont disponibles :

- **🎯 Objectif du chapitre** enrichi avec questions structurantes (3–5 max)
- **⚠️ À retenir** en encadré, pour les nuances importantes
- **Code samples** PHP/Twig réalistes (PHP 8.4+, `declare(strict_types=1)`, attributs `#[AsLiveComponent]` etc.) — **toujours annoter le code** et expliquer le pourquoi
- **Tableaux** comparatifs, récap, grilles de critères
- **Diagrammes ASCII** pour l'architecture, les flux, les cycles
- **💬 Message clé** sous forme de citation (`> "..."`)
- **🗣️ Narration (script oral)** : 1–2 paragraphes dictés à voix haute, comme parlé en conférence
- **🧭 Transition vers le chapitre N+1** : 1–2 phrases qui amorcent la suite

### 4. S'appuyer sur la documentation officielle

Pour les chapitres qui touchent Twig Components, Live Components, Stimulus, Turbo, AssetMapper, **vérifier les noms d'attributs, options et comportements** sur les sources officielles plutôt que de s'en remettre à la mémoire :

- https://symfony.com/bundles/ux-twig-component/current/index.html
- https://symfony.com/bundles/ux-live-component/current/index.html
- https://ux.symfony.com/
- https://symfony.com/doc (chapitres pertinents : Twig, Stimulus, AssetMapper, Turbo)

Utiliser **WebFetch** sur ces URLs quand un détail technique précis est en jeu (signature d'attribut, option par défaut, hook du cycle de vie). Pas besoin de webfetch systématique — seulement quand on est dans le flou.

> Pourquoi : ces APIs évoluent. Mieux vaut une vérification ciblée qu'un exemple plausible mais désynchronisé.

### 5. Style et voix

- **Langue** : français, **tous les accents et diacritiques préservés** (jamais "decoupe" pour "découpe", "ecosysteme" pour "écosystème")
- **Ton** : pédagogique mais punchy, comme un talk en live — phrases courtes, pas de jargon gratuit
- **Emojis** : modérés, surtout en têtes de section (🎯 ⚠️ ✅ ❌ 💬 🗣️ 🧭) — pas dans le corps de texte
- **Code** : `declare(strict_types=1)`, attributs PHP 8, conventions du projet (cf. `CLAUDE.md` : entités dans `src/Entity/`, fixtures dans `fixtures/`, etc.)
- **Vocabulaire récurrent à honorer** : "server-driven", "DOM morphing", "progressive enhancement", "opt-in", "DX", "props typées"
- **Pas de remplissage** : si une section n'apporte rien, ne pas l'écrire pour faire du volume

### 6. Cohérence narrative inter-chapitres

- Reprendre le **vocabulaire déjà posé** dans les chapitres précédents (ex. "les trois douleurs" du chapitre 2)
- Faire des **callbacks explicites** ("comme on l'a vu au chapitre 3...") quand un concept antérieur revient
- La **transition de fin** doit s'enchaîner naturellement avec l'objectif du chapitre suivant — vérifier en lisant l'intro du chapitre n+1

### 7. Avant d'écrire : proposer un plan

Sauf si l'enrichissement est trivial, **annoncer brièvement** à l'utilisateur :
- Le type de chapitre identifié
- La structure d'enrichissement choisie (quelles sections vont être ajoutées/réécrites)
- Les vérifications externes prévues (URLs Symfony à fetch si applicable)

L'utilisateur peut rectifier avant que tu écrives 400 lignes dans la mauvaise direction.

### 8. Écriture

- Utiliser **Write** pour réécrire le fichier complet (les enrichissements sont substantiels — un Edit ciblé est rarement adapté).
- **Conserver les sections existantes pertinentes** plutôt que les écraser sans raison (le squelette d'origine porte souvent l'intention initiale de l'auteur).
- Garder une **densité comparable** aux chapitres 02/04 : ~300–450 lignes pour un chapitre standard, plus pour les chapitres centraux (Live Components, démo).

### 9. Après écriture

Résumer en 3–5 bullets ce qui a été ajouté/restructuré, pour que l'utilisateur puisse rapidement se faire une idée et demander des ajustements.

## Anti-patterns à éviter

- ❌ Plaquer la structure du chapitre 02 sur un chapitre conclusion ou démo (ça ne colle pas)
- ❌ Inventer des noms d'attributs ou d'options Symfony UX au lieu de vérifier
- ❌ Multiplier les emojis dans le corps du texte (ils servent uniquement à baliser les sections)
- ❌ Écrire "this chapter discusses..." → on est en français, et on parle au lecteur, pas de la doc
- ❌ Écrire des paragraphes-fleuves : préférer listes, tableaux, exemples
- ❌ Faire du volume vide avec des reformulations triviales — la densité doit être informationnelle
- ❌ Casser la continuité narrative en réintroduisant un concept déjà posé sans le rappeler

## Références bundlées

- `references/style-guide.md` : extraits commentés des chapitres 02 et 04 montrant les conventions de style à reproduire
