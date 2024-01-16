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
require_once 'vendor/splash/php-bundle/src/Tests/WebTestCase.php';
//====================================================================//
// FIX - Disable Versioning on Delete
$akeneoDevPath = "vendor/akeneo/pim-community-dev/";
copy(
    'vendor/splash/akeneo-bundle/tests/fixes/AddRemoveVersionSubscriber.php',
    $akeneoDevPath.'src/Akeneo/Tool/Bundle/VersioningBundle/EventSubscriber/AddRemoveVersionSubscriber.php'
);
//====================================================================//
// FIX - Disable Akeneo Test Services
if (is_file($akeneoDevPath."config/services/test/test_services.yml")) {
    unlink($akeneoDevPath."config/services/test/test_services.yml");
}
//====================================================================//
// Use Akeneo Bootstrap File
require 'config/bootstrap.php';
