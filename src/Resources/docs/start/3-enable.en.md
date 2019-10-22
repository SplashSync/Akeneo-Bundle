---
lang: en
permalink: start/enable
title: Enable the Bundle
---

### Activate Splash Bundle in your App Kernel

Now that bundle is installed and configured, you have to activate it on Symfony Kernel.

Two bundles needs to be activated, your Akeneo Bundle, plus our fundation bundle for Symfony.


```php

    /**
     * Registers your custom bundles
     *
     * @return array
     */
    protected function registerProjectBundles()
    {
        return array(
            new \Splash\Bundle\SplashBundle(),
            new \Splash\Akeneo\SplashAkeneoBundle(),
        );
    }

```


### Clear Cache & Restart your application

To ensure changes are done, you need to flush the cache.

Diplomatic method...
```bash
$ php bin/console --env=prod cache:clear --no-warmup
```

Less Diplomatic method...
```bash
$ rm -Rf var/cache/*
```


### Test & Declare your server

At this step, your akeneo application should work normaly!

Let's check Splash is correctly installed & declare our server configuration to Splash.

In your browser, go to **https://akeneo.exemple.com/ws/splash?node=ThisIsSplashWsId**

![]({{ "/assets/img/server-test-ok.png"|relative_url}})

> This must work even if you are disconnected from Akeneo.

### See more details ?

Now sign in Akeneo, and go to **https://akeneo.exemple.com/ws/splash-test?node=ThisIsSplashWsId**

![]({{ "/assets/img/server-details.png"|relative_url}})

<div class="warning">
	This must work <b>only</b> if you are connected to Akeneo.
</div>
