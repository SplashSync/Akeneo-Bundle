---
lang: fr
permalink: start/setup
title: Configurer le Bundle
---

### Ajouter une configuration pour votre serveur

Une fois que vous avez créé votre serveur sur un compte Splash, vous pouvez configurer votre application pour l'utiliser avec.

Voici une configuration par défaut.

```yaml
# app/config/config.yml
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
                    logo:       https://akeneo.exemple.com/bundles/pimui/images/info-user.png
```

Plus de détails sur la configuration et les options dans la documentation principale.

### Ajouter les routes Splash

Pour accéder à votre serveur, nous devons ajouter des routes spécifiques dans la configuration de Symfony.


```yaml
# app/config/routing.yml
splash_ws:
    resource: "@SplashBundle/Resources/config/routing.yml"
    prefix: /ws
```

Vous pouvez personnaliser le chemin où se trouvent les routes de Splash, mais vous devez vous soucier des répercussions sur le reste du processus de configuration.

### Activer l'accès public pour les routes Splash

Par défaut, tous les accès à Akeneo sont effectués après authentification.
Splash nécessite un accès public aux routes du web-service. Nous devons donc créer un pare-feu dédié.

Pour ce faire, créez un nouveau pare-feu dans Symfony Security.

```yaml
# app/config/security.yml
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
