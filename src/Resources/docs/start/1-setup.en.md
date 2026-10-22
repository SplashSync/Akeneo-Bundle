---
lang: en
permalink: start/setup
title: Configure the Bundle
---

### Add configuration for your Server

Once you have created your server on Splash account, you can setup your application to work with it.

Here is a default configuration.

```yaml
# config/packages/splash.yml
splash:
    connections:
        akeneo:
            name:           Akeneo for Splash
            id:             ThisIsSplashWsId
            key:            ThisIsYourEncryptionKeyForSplash
            config:
                locale:         en_US
                channel:        ecommerce
                currency:       EUR
                images:         ['image', 'variation_image']
    infos:
        ico:            '%kernel.project_dir%/vendor/splash/akeneo-bundle/src/Resources/img/logo.png'
        logo:           https://akeneo.exemple.com/bundles/pimui/images/info-user.png
```

More details on configuration & options in the main documentation.

### Add Splash Routing

In order to access your server, we need add specific routes in Symfony configuration.

```yaml
# config/routes/splash.yml
splash_ws:
    resource:       "@SplashBundle/Resources/config/routing.yml"
    prefix:         /ws
```

You can customize the path were splash routes are located, but take care of the impacts on rest of the configuration process.

### Enable Public Access for Splash Routes

By default, all access to Akeneo are done after authentication.
Splash requires a public access to webservices routes, so we need to create a dedicated firewall.

To do that, add a new firewall in your project security configuration.
Akeneo firewalls are defined in `vendor/akeneo/pim-community-dev/config/packages/security.yml`:
copy them in your project file if it does not override them yet.

```yaml
# config/packages/security.yml
security:
    firewalls:
        # Public Access to Webservices Urls
        splash:
            pattern:                        ^/ws/splash$
            security:                       false
            stateless:                      true

        # !! Make sure this firewall is set before the main firewall. !!
        main:
            pattern:                        ^/
            provider:                       chain_provider
```
