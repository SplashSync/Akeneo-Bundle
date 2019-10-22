---
lang: en
permalink: docs/configuration
title: Main Configuration
---

### Change default Locale

You can change main locale used by Splash Module for fields definition and synchronization.

Other available languages will remain available, but with indexed tags that may no be available for opther applications.

Whatever, all available languages will be accessible with Splash.

Default language is set here: 

```yaml
# app/config/config.yml

splash:
    connections:
        akeneo:    
            config:
                locale:         en_US
```


### Select the channel to use

Each connection with Splash uses data of a single channel. Attributes values of other channels won't be impacted by Splash.

By default, Splash will connect to ecommerce channel, available in Akeneo demo fixtures. 

You can change this parameter here: 

```yaml
# app/config/config.yml

splash:
    connections:
        akeneo:    
            config:
                channel:        ecommerce
```

### Select your default currency

By default, Splash will use Euro, feel free to change to your default currency.

You can change this parameter here: 

```yaml
# app/config/config.yml
splash:
    connections:
        akeneo:    
            config:
                currency:       EUR
```

### Select attributes used for product images gallery

If you want to synchronize your Products Images with other platforms through Splash. 
You have to tell the bundle which attribute will be used for populating the image gallery.

You can setup the image gallery here: 

```yaml
# app/config/config.yml
splash:
    connections:
        akeneo:    
            config:
                images:         ['image', 'variation_image']
```