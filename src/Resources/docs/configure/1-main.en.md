---
lang: en
permalink: configure/main
title: Main Configuration
description: Locale, channel, currency, images attributes & multi-select separator used by the connector.
updated: 2026-11-20
---

### Change default Locale

You can change main locale used by Splash Module for fields definition and synchronization.

Other available languages will remain available, but with indexed tags that may not be available for other applications.

Whatever, all available languages will be accessible with Splash.

Default language is set here:

```yaml
# config/packages/splash.yml
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
# config/packages/splash.yml
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
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                currency:       EUR
```

### Select attributes used for product images gallery

If you want to synchronize your Products Images with other platforms through Splash,
you have to tell the bundle which attributes will be used for populating the image gallery.

You can setup the image gallery here:

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                images:         ['image', 'variation_image']
```

Each image may also read its label from another product attribute.
If no label attribute is given, or if it is empty, the image attribute label is used.

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

### Multi-select values as text

Each multi-select attribute is also exposed as a read-only text field, named with the `ms2s_` prefix (e.g. `ms2s_languages`).
It contains the translated option labels, in each available language, joined by a separator: `English | French | Spanish`.

Use it when the target application expects a plain text field instead of a list.
An option without translation is written with its code.

The default separator is ` | ` (space, vertical bar, space). You can change it here:

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                multiselect_separator:  ", "
```
