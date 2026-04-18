# 🧠 1. Introduction — Pourquoi parler de composants ?

## 🎯 Objectif du chapitre

Poser le **contexte** et le **problème initial** qui a poussé l'écosystème PHP/Symfony vers une approche orientée composants.

---

## Slide 1.1 — Le problème initial

### Constat

- **Complexité croissante** des templates Twig sur les projets matures
- **Duplication de markup** : cards, alerts, modals, forms répétés partout
- **Difficulté à maintenir / tester / réutiliser** les blocs d'UI
- **Mélange logique métier / présentation** dans les templates

### Symptômes concrets

- Un `alert.html.twig` copié-collé dans 15 templates
- Des `if` imbriqués dans le Twig pour gérer les variantes
- Pas de contrat clair sur les "props" attendues
- Régressions visuelles à chaque refacto

---

## 💬 Message clé

> **"Avant, Twig = templates statiques + duplication + logique dispersée"**

On avait un langage de template puissant, mais **pas de modèle de composant**.

---

## 🗣️ Narration (script oral)

> "On va parler aujourd'hui d'un changement de paradigme côté Symfony. Pendant longtemps, on a fait du Twig "à l'ancienne" : des includes, des macros, un peu de logique ici et là. Ça marche, mais dès que le projet grossit, on voit apparaître les mêmes problèmes que ceux qui ont poussé le front à adopter React ou Vue : duplication, couplage, intestabilité. La question devient : comment ramener les bénéfices du modèle composant **côté serveur**, sans tout réécrire en SPA ?"

---

## 🧭 Transition vers le chapitre 2

Avant de parler de la solution moderne (Symfony UX), regardons ce qu'on faisait **historiquement** pour résoudre ces problèmes — et pourquoi ces approches ont montré leurs limites.
