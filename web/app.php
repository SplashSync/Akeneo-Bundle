<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';
$kernel = new AppKernel('prod', false);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
$request = Request::createFromGlobals();

$loadBalancerTrustedIPs = getenv('TRUSTED_PROXY_IPS');
if (!empty($loadBalancerTrustedIPs)) {
    $ipsArray = explode(',', $loadBalancerTrustedIPs);
    Request::setTrustedProxies(
        // the IP address (or range) of your proxy
        $ipsArray,
        // trust *all* "X-Forwarded-*" headers
        Request::HEADER_X_FORWARDED_ALL
    );
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
