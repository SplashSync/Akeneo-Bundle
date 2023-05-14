<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

//====================================================================//
// PHPUNIT Bootstrap File for Akeneo Plugin
//====================================================================//

//====================================================================//
// Force Quite Error Reporting
ini_set("error_reporting", "E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED");
//====================================================================//
// Register Splash Test case
require_once 'vendor/splash/php-bundle/src/Tests/KernelTestCase.php';
//====================================================================//
// FIX - Disable Versioning on Delete
copy(
    'vendor/splash/akeneo-bundle/tests/fixes/AddRemoveVersionSubscriber.php',
    'src/Akeneo/Tool/Bundle/VersioningBundle/EventSubscriber/AddRemoveVersionSubscriber.php'
);
//====================================================================//
// Use Akeneo Bootstrap File
require 'config/bootstrap.php';
