# Akeneo-Bundle
Splash Bundle for Akeneo PIM Solution


[![N|Solid](https://github.com/SplashSync/Php-Core/blob/master/Resources/img/fake-image2.jpg)](http://www.splashsync.com)
# Splash Sync Bundle for Akeneo
Splash Php Bundle for Akeneo PIM.

This module implement Splash Sync connector for Akeneo. 
It provide access to Products Objects for automated synchonisation though Splash Sync dedicated protocol.

[![Build Status](https://travis-ci.org/SplashSync/Prestashop.svg?branch=master)](https://travis-ci.org/SplashSync/Akeneo-Bundle)
[![Latest Stable Version](https://poser.pugx.org/splash/prestashop/v/stable)](https://packagist.org/packages/splash/akeneo-bundle)
[![Latest Unstable Version](https://poser.pugx.org/splash/prestashop/v/unstable)](https://packagist.org/packages/splash/akeneo-bundle)
[![License](https://poser.pugx.org/splash/prestashop/license)](https://packagist.org/packages/splash/akeneo-bundle)

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

* PHP 7.1+
* Akeneo 2.0+
* An active Splash Sync User Account

## Documentation

For the configuration guide and reference, see: [Akeneo Bundle Docmumentation](https://splashsync.github.io/Akeneo-Bundle/)

## Contributing

Any Pull requests are welcome! 

This module is part of [SplashSync](http://www.splashsync.com) project.
