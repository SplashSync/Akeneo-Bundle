---
lang: fr
permalink: docs/configuration
title: Configuration Principale
---

### Modifier les paramètres régionaux par défaut

Vous pouvez modifier les paramètres régionaux principaux utilisés par le module Splash pour la définition et la synchronisation des champs.

Les autres langues disponibles resteront disponibles, mais avec des balises indexées qui pourraient ne pas être disponibles pour les applications opther.

Quoi qu'il en soit, toutes les langues disponibles seront accessibles avec Splash.

La langue par défaut est définie ici:

```yaml
# app/config/config.yml
splash:
    connections:
        akeneo:    
            config:
                locale:         en_US
```

### Sélectionnez le canal à utiliser

Chaque connexion avec Splash utilise les données d'un seul canal. Les valeurs des attributs des autres canaux ne seront pas affectées par Splash.

Par défaut, Splash se connectera au canal de commerce électronique, disponible dans les appareils de démonstration Akeneo.

Vous pouvez modifier ce paramètre ici:

```yaml
# app/config/config.yml
splash:
    connections:
        akeneo:    
            config:
                channel:        ecommerce
```

### Sélectionnez votre devise par défaut

Par défaut, Splash utilisera l'euro, n'hésitez pas à changer pour votre devise par défaut.

Vous pouvez modifier ce paramètre ici:

```yaml
# app/config/config.yml
splash:
    connections:
        akeneo:    
            config:
                currency:       EUR
```

### Sélection des attributs utilisés pour la galerie d'images de produit

Si vous souhaitez synchroniser vos images de produits avec d'autres plates-formes via Splash.
Vous devez indiquer au lot quel attribut sera utilisé pour remplir la galerie d'images.

Vous pouvez configurer la galerie d'images ici:

```yaml
# app/config/config.yml
splash:
    connections:
        akeneo:    
            config:
                images:         ['image', 'variation_image']
```