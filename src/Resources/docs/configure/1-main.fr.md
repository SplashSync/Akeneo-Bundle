---
lang: fr
permalink: configure/main
title: Configuration Principale
description: Langue, canal, devise et attributs images utilisés par le connecteur.
updated: 2026-10-22
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
