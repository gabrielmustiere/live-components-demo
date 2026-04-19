# Guide de style — chapitres `docs/plan/`

Référence rapide des conventions de style utilisées dans les chapitres déjà enrichis (02 et 04). À consulter quand un doute survient sur la mise en forme ou le ton.

---

## Structure type d'une slide

```markdown
## Slide N.X — Titre court et descriptif

### Sous-titre / angle

> Citation ou postulat fort (optionnel)

### Exemple minimal

\`\`\`twig
{# code annoté en commentaires #}
\`\`\`

### Ce que ça résout

- ✅ Bénéfice concret 1
- ✅ Bénéfice concret 2

### Ce que ça ne résout PAS

- ❌ Limite 1 (avec explication courte)
- ❌ Limite 2

### Cas d'usage légitimes

- Situation réelle 1
- Situation réelle 2
```

Ce moule s'applique aux chapitres "inventaire" (02) ou "tour des features". Il **n'est pas obligatoire** pour les autres types de chapitres.

---

## En-têtes de section récurrents

| Emoji | Section | Usage |
|-------|---------|-------|
| 🎯 | Objectif du chapitre | En tête de chapitre |
| 🧭 | Vue d'ensemble / Transition | Cadrage initial ou pont vers chapitre suivant |
| 🧱 / 🧩 | Titres de chapitre | Au choix selon thème |
| ⚠️ | À retenir | Encadré nuance importante |
| 💬 | Message clé | Citation finale |
| 🗣️ | Narration (script oral) | Texte parlé en conférence |
| 🧠 | Le vrai problème de fond | Synthèse profonde (optionnel) |

Dans le **corps** du texte, pas d'emoji. Seulement en titres de section et dans les listes ✅/❌/⚠️.

---

## Tableaux — formats standards

### Grille de critères (chapitre 02)

```markdown
| Critère | Question posée |
|---------|----------------|
| **Encapsulation logique** | Peut-on mettre du code PHP métier associé ? |
```

### Récapitulatif comparatif

```markdown
| Approche | Logique PHP | Template séparé | Props typées | État | Perf |
|----------|:-----------:|:---------------:|:------------:|:----:|:----:|
| `include` | ❌ | ✅ | ❌ | ❌ | ✅ |
```

### Comparaison avec voisins

```markdown
| Solution | Langage | Modèle | Forces | Limites |
|----------|---------|--------|--------|---------|
| **Symfony UX** | PHP + Twig | Server-driven | DX native | 1 req/interaction |
```

---

## Code samples — règles

- **Toujours** annoter via commentaires Twig (`{# … #}`) ou PHP (`// …`)
- Utiliser `declare(strict_types=1)`, `final class`, types stricts
- Préférer attributs PHP 8 (`#[AsLiveComponent]`, `#[LiveProp]`, `#[LiveAction]`)
- Imports raisonnables (pas de chemin complet `App\\Controller\\Foo` dans les exemples sauf besoin pédagogique précis)
- Éviter les exemples triviaux (`Hello World`) — ancrer dans un domaine réaliste (panier, formulaire, dashboard, recherche)

---

## Diagrammes ASCII

```
┌──────────────────┐    ┌──────────────────┐
│ Twig Components  │───▶│ Live Components  │
│   (statique)     │    │    (réactif)     │
└──────────────────┘    └──────────────────┘
```

- Caractères Unicode box-drawing (`┌ ┐ └ ┘ │ ─ ▶`)
- Labels courts, alignés
- Toujours **commenter le schéma** en dessous (lecture, légende)

---

## Narration orale

Format type :

```markdown
## 🗣️ Narration (script oral)

> "Phrase d'attaque qui pose le contexte. Ensuite on déroule le raisonnement comme si on parlait au public — phrases courtes, tournures parlées. On peut faire référence au schéma ou au tableau qu'on vient de montrer.
>
> Deuxième paragraphe optionnel pour amener la transition. Conclure par une phrase qui crée l'envie d'enchaîner sur la suite."
```

- **À la première personne du pluriel** ("on", "nous") plutôt que "vous" ou "tu"
- **Ton parlé** mais propre (pas de "euh", pas de familiarités excessives)
- **Pas de métadonnée** ("ici je parle de…") — le texte est ce qu'on dit, pas une description de ce qu'on dit

---

## Transitions entre chapitres

Format type :

```markdown
## 🧭 Transition vers le chapitre N+1

Une ou deux phrases qui annoncent **précisément** ce qui vient ensuite et pourquoi c'est la suite logique. Idéalement reprendre un mot-clé qui apparaîtra dans l'objectif du chapitre suivant.
```

---

## Vocabulaire à honorer (cohérence inter-chapitres)

- **server-driven** : modèle où la vérité reste côté serveur
- **DOM morphing** : ce que fait `live_controller.js` sur la réponse
- **progressive enhancement** : adoption brique par brique
- **opt-in** : aucune brique n'en impose une autre
- **DX** : Developer Experience
- **props typées** : propriétés PHP avec types stricts qui servent de contrat
- **les trois douleurs** : référence au chapitre 02 (typage, organisation, réactivité)
- **HTML-over-the-wire** : philosophie commune Hotwire / UX
- **îlot** : composant SPA isolé dans une page Symfony (ux-react, ux-vue)

---

## Longueur cible

| Type de chapitre | Lignes markdown |
|------------------|-----------------|
| Introduction / conclusion | 200–300 |
| Inventaire / théorique | 350–450 |
| Brique technique centrale (Live, Twig Components) | 450–600 |
| Démo live | 300–400 (script structuré, pas dilué) |

Ces fourchettes sont indicatives. Plutôt **dense** que long.
