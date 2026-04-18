# 🧱 2. Avant Symfony UX — Les solutions historiques

## 🎯 Objectif du chapitre

Faire l'**archéologie** des solutions utilisées avant Symfony UX pour comprendre **pourquoi elles ne suffisent plus**.

---

## Slide 2.1 — Twig includes / macros

### Exemple

```twig
{# templates/components/_alert.html.twig #}
<div class="alert alert-{{ type|default('info') }}">
    {{ message }}
</div>

{# Utilisation #}
{{ include('components/_alert.html.twig', { type: 'error', message: 'Oops' }) }}
```

### Limites

- Réutilisation **limitée** au template
- **Aucune logique encapsulée** côté PHP
- Pas de contrat fort sur les props (tout est un tableau)
- Pas de validation, pas d'autocomplétion IDE
- Les macros : un peu mieux, mais toujours **sans état ni services**

---

## Slide 2.2 — `render(controller())`

### Exemple

```twig
{{ render(controller('App\\Controller\\CartController::miniCart')) }}
```

### Limites

- **Sous-requête HTTP interne** (overhead non négligeable)
- Couplage fort au cycle HTTP (session, request stack…)
- Mauvaise perf sur des pages avec beaucoup de composants
- DX médiocre : chaque "composant" = un contrôleur

---

## Slide 2.3 — Twig functions / extensions custom

### Exemple

```php
// TwigExtension
public function renderBadge(string $label, string $color): string
{
    return sprintf('<span class="badge badge-%s">%s</span>', $color, $label);
}
```

### Limites

- Génération de HTML **en string PHP** → illisible
- Pas orienté composant (pas de template séparé)
- Testabilité compliquée
- Mélange logique / markup dans du PHP

---

## 💬 Message clé (slide conclusion)

> **"On bricolait des composants… sans vrai modèle de composant."**

Chaque approche résout **un morceau** du problème, jamais l'ensemble :

| Approche | Encapsulation logique | Template séparé | Réutilisable | Performant |
|----------|----------------------|-----------------|--------------|------------|
| include / macro | ❌ | ✅ | ⚠️ | ✅ |
| render(controller()) | ✅ | ✅ | ⚠️ | ❌ |
| Twig extension | ⚠️ | ❌ | ✅ | ✅ |

---

## 🗣️ Narration (script oral)

> "Ces solutions, on les a toutes utilisées. Elles marchent, mais aucune ne donne un **vrai modèle composant** : quelque chose qui combine une classe PHP, un template, des props typées, et qui soit réutilisable sans friction. C'est exactement le vide que Symfony UX est venu combler — en s'inspirant largement du modèle qui a fait le succès du front moderne."

---

## 🧭 Transition vers le chapitre 3

Regardons justement **pourquoi le modèle composant a gagné côté JS**, et quels concepts on va vouloir ramener côté serveur.
