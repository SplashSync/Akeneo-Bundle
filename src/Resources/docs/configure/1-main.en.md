---
lang: en
permalink: configure/main
title: Main Configuration
description: Locale, channel, currency & prices as doubles, images, categories filters, multi-select separator & extra metrics used by the connector.
updated: 2026-11-26
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

Each price attribute is also exposed as a read-only `double` field, named with the `p2d_` prefix (e.g. `p2d_price`).
It holds the tax excluded amount in this currency, as stored in Akeneo, and uses the same name and microdata as the price field.

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

### Filter products by categories

By default, all products are synchronized with Splash.

You can restrict the connection to some branches of your categories trees by listing their codes.
A product is synchronized if at least one of its categories is a listed category, or one of its sub-categories.

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                categories:     ['tvs_projectors', 'cameras']
```

When this filter is active:

* products outside these categories are not listed and cannot be read by Splash;
* their changes are not sent to Splash;
* the connector self-test checks the integrity of these categories trees, and warns if errors are found.

Leave the list empty, or remove the parameter, to synchronize all products.

### Categories from all trees

By default, Splash only works with the categories tree of the configured channel:
categories from other trees are not listed, and are ignored on products categories fields.

To work with all your categories trees, disable this filter:

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            config:
                channel_categories:     false
```

This parameter only changes which categories are visible. To restrict which products are synchronized, use the `categories` parameter above.

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

### Metrics in other units

Metric attributes are exposed in the base unit of their family (e.g. meters, kilograms).
This value is shared with all your connected applications and is never changed.

When an application expects another unit, or an integer, you can add extra read-only fields.
Each entry converts a metric attribute to a target unit, and exposes it as an integer (`int`, rounded) or a decimal (`double`, default).

The field identifier is the entry key, prefixed with `m2u_` (e.g. `m2u_width_cm`).
The field is named after the attribute label, followed by the unit symbol (e.g. `Width (cm)`):

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

You may also give the identifier name explicitly, with a list of entries:

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

* `name`: letters, digits and underscores only. Overrides the entry key when both are given.
* `unit`: an Akeneo unit code of the attribute measurement family (e.g. `CENTIMETER`, `INCH`, `GRAM`).
* Invalid entries (unknown attribute, unit from another family...) are ignored, with a warning in Splash logs.
