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

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * @var ClassLoader $loader 
 */
$loader = require __DIR__.'/../vendor/autoload.php';

// add possibility to extends doctrine unit test and use mocks
$loader->add('Doctrine\\Tests', __DIR__.'/../vendor/doctrine/orm/tests');

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
