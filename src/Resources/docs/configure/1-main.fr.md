---
lang: fr
permalink: configure/main
title: Configuration Principale
description: Langue, canal, devise, images, séparateur des multi-sélections et mesures supplémentaires utilisés par le connecteur.
updated: 2026-11-24
---

### Modifier la langue par défaut

Vous pouvez modifier la langue principale utilisée par le module Splash pour la définition et la synchronisation des champs.

Les autres langues resteront disponibles, mais avec des balises indexées qui pourraient ne pas être reconnues par les autres applications.

Quoi qu'il en soit, toutes les langues disponibles seront accessibles avec Splash.

La langue par défaut est définie ici :

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                locale:         en_US
```

### Sélectionnez le canal à utiliser

Chaque connexion avec Splash utilise les données d'un seul canal. Les valeurs des attributs des autres canaux ne seront pas affectées par Splash.

Par défaut, Splash se connectera au canal ecommerce, disponible dans les données de démonstration d'Akeneo.

Vous pouvez modifier ce paramètre ici :

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                channel:        ecommerce
```

### Sélectionnez votre devise par défaut

Par défaut, Splash utilisera l'euro, n'hésitez pas à changer pour votre devise par défaut.

Vous pouvez modifier ce paramètre ici :

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                currency:       EUR
```

### Sélection des attributs utilisés pour la galerie d'images de produit

Si vous souhaitez synchroniser vos images de produits avec d'autres plateformes via Splash,
vous devez indiquer au bundle quels attributs seront utilisés pour remplir la galerie d'images.

Vous pouvez configurer la galerie d'images ici :

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                images:         ['image', 'variation_image']
```

Chaque image peut aussi lire son libellé dans un autre attribut du produit.
Si aucun attribut de libellé n'est indiqué, ou s'il est vide, le libellé de l'attribut image est utilisé.

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                images:
                    - { code: 'image', label: 'meta_title' }
                    - 'variation_image'
```

### Valeurs des multi-sélections en texte

Chaque attribut à sélection multiple est aussi exposé sous forme d'un champ texte en lecture seule, préfixé par `ms2s_` (par exemple `ms2s_languages`).
Il contient les libellés traduits des options, dans chaque langue disponible, reliés par un séparateur : `English | French | Spanish`.

Utilisez-le lorsque l'application cible attend un simple champ texte plutôt qu'une liste.
Une option sans traduction est écrite avec son code.

Le séparateur par défaut est ` | ` (espace, barre verticale, espace). Vous pouvez le modifier ici :

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                multiselect_separator:  ", "
```

### Mesures dans d'autres unités

Les attributs de type mesure sont exposés dans l'unité de base de leur famille (par exemple mètres, kilogrammes).
Cette valeur est partagée avec toutes vos applications connectées et n'est jamais modifiée.

Lorsqu'une application attend une autre unité, ou un entier, vous pouvez ajouter des champs supplémentaires en lecture seule.
Chaque entrée convertit un attribut de mesure dans une unité cible, et l'expose en entier (`int`, arrondi) ou en décimal (`double`, par défaut).

L'identifiant du champ est la clé de l'entrée, préfixée par `m2u_` (par exemple `m2u_width_cm`).
Le champ porte le libellé de l'attribut, suivi du symbole de l'unité (par exemple `Width (cm)`) :

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                extra_metrics:
                    width_cm:       { attribute: 'width',             unit: 'CENTIMETER',  format: 'int' }
                    width_inch:     { attribute: 'width',             unit: 'INCH' }
                    diagonal_inch:  { attribute: 'display_diagonal',  unit: 'INCH',        format: 'int' }
```

Vous pouvez aussi donner le nom de l'identifiant explicitement, avec une liste d'entrées :

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                extra_metrics:
                    - { name: 'width_cm',  attribute: 'width',   unit: 'CENTIMETER',  format: 'int' }
                    - { name: 'weight_g',  attribute: 'weight',  unit: 'GRAM',        format: 'int' }
```

* `name` : lettres, chiffres et tirets bas uniquement. Prend le pas sur la clé de l'entrée si les deux sont donnés.
* `unit` : un code d'unité Akeneo de la famille de mesure de l'attribut (par exemple `CENTIMETER`, `INCH`, `GRAM`).
* Les entrées invalides (attribut inconnu, unité d'une autre famille...) sont ignorées, avec un avertissement dans les logs Splash.
