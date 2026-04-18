# 🧩 4. Symfony UX — La réponse côté PHP

## 🎯 Objectif du chapitre

Présenter la **vision** Symfony UX et positionner le projet dans l'écosystème.

---

## Slide 4.1 — La vision Symfony UX

### Postulat

> **"Ramener le modèle composant côté serveur, sans renier PHP ni Twig."**

### Principes directeurs

- **Garder PHP + Twig** comme langages principaux
- **Réduire le besoin de JS** à son strict minimum
- S'inspirer des **frameworks modernes** (React, Vue, Livewire, LiveView)
- Offrir une **DX Symfony-native** (DI, autowiring, attributs PHP 8)

---

## Slide 4.2 — Symfony UX en un schéma

```
┌────────────────────────────────────────────────────┐
│                    Symfony UX                      │
│                                                    │
│   ┌──────────────────┐    ┌──────────────────┐     │
│   │ Twig Components  │───▶│ Live Components  │     │
│   │    (statique)    │    │   (réactif)      │     │
│   └──────────────────┘    └──────────────────┘     │
│                                                    │
│   Stimulus (JS minimal côté client)                │
│   Turbo (navigation sans rechargement)             │
│   + d'autres packages (Chartjs, Dropzone, etc.)    │
└────────────────────────────────────────────────────┘
```

### Positionnement

- **Bridge** entre backend classique et frontend moderne
- **Progressive enhancement** : tu peux adopter chaque brique indépendamment
- **Opt-in** : aucune obligation de tout utiliser

---

## Slide 4.3 — Ce que Symfony UX n'est PAS

- ❌ **Pas un SPA framework** (on ne remplace pas React)
- ❌ **Pas un remplaçant de Stimulus** (il s'appuie dessus)
- ❌ **Pas une solution miracle** pour toutes les UI interactives

### Ce que Symfony UX EST

- ✅ Une **couche composant** pour Twig
- ✅ Un **modèle réactif server-driven** (via Live Components)
- ✅ Un **écosystème de bundles** autour d'un socle commun

---

## 💬 Message clé

> **"Symfony UX = bridge backend ↔ frontend."**
> On garde la productivité Symfony, on gagne le modèle composant, on évite la complexité SPA quand elle n'est pas nécessaire.

---

## 🗣️ Narration (script oral)

> "L'approche de Symfony UX est pragmatique : plutôt que de réinventer la roue, ils se sont posé la question "qu'est-ce qui marche dans les frameworks modernes ?" et ont porté ces idées en PHP. Le résultat, c'est deux briques principales qu'on va explorer : **Twig Components** pour la composition statique, et **Live Components** pour l'interactivité. Les deux cohabitent parfaitement."

---

## 🧭 Transition vers le chapitre 5

Attaquons la première brique : **Twig Components**, le socle de tout l'édifice.
