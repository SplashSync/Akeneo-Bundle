[![N|Solid](https://github.com/SplashSync/Php-Core/raw/master/img/github.jpg)](https://www.splashsync.com)

# Splash Sync Bundle for Akeneo
Splash Php Bundle for Akeneo PIM.

This module implement Splash Sync connector for Akeneo. 
It provides access to Products Objects for automated synchronisation though Splash Sync dedicated protocol.

[![Latest Stable Version](https://poser.pugx.org/splash/akeneo-bundle/v/stable)](https://packagist.org/packages/splash/akeneo-bundle)
[![Latest Unstable Version](https://poser.pugx.org/splash/akeneo-bundle/v/unstable)](https://packagist.org/packages/splash/akeneo-bundle)
[![License](https://poser.pugx.org/splash/akeneo-bundle/license)](https://packagist.org/packages/splash/akeneo-bundle)

## Branches

Branch | Akeneo | PHP | Status | Install |
------ | ------ | --- | ------ | ------ |
master   |  4.0&5.0 | 7.3+ | Active | composer require splash/akeneo-bundle |
3.0   |  3.0 | 7.2 | Active | composer require splash/phpcore:dev-master splash/php-bundle:1.0.0 splash/akeneo-bundle:3.0.x-dev |
2.3   |  2.3 | 7.1 | Deprecated | composer require splash/phpcore:dev-master splash/php-bundle:1.0.0 splash/akeneo-bundle:2.3.x-dev |
2.1   |  2.0, 2.1 | 7.1 | Deprecated | composer require splash/phpcore:dev-master splash/php-bundle:1.0.0 splash/akeneo-bundle:2.1.x-dev |

## Installation via Composer

Download Akeneo-Bundle and its dependencies to the vendor directory. You can use Composer for the automated process:

```bash
$ php composer.phar require splash/akeneo-bundle
```

Composer will install the bundle to `vendor/splash` directory.

### Adding bundle to your application kernel

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
            new \Splash\Bundle\SplashBundle(),          // Splash Sync Core PHP Bundle 
            new \Splash\Akeneo\SplashAkeneoBundle(),    // Splash Bundle for Akeneo
        // ...
    );
}
```

### Configure Splash Bundles

Here is the default configuration for Splash bundles:

```yml
splash:
    connections:
        akeneo:    
            name:           Akeneo for Splash
            id:             ThisIsSplashWsId                    # Your Splash Server Id
            key:            ThisIsYourEncryptionKeyForSplash    # Your Server Secret Encryption Key
            config:
                locale:         en_US
                channel:        ecommerce
                currency:       EUR
```

## Requirements

* PHP 7.2+
* Akeneo 3.0+
* An active Splash Sync User Account

## Documentation

For the configuration guide and reference, see: [Akeneo Bundle Docmumentation](https://splashsync.github.io/Akeneo-Bundle/)

## Contributing

Any Pull requests are welcome! 

This module is part of [SplashSync](http://www.splashsync.com) project.
