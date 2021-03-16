#!/bin/sh
################################################################################
#
# Copyright (C) 2021 BadPixxel <www.badpixxel.com>
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#
################################################################################

echo "----------------------------------------------------"
echo "--> AKENEO - Install Splash Bundle"
echo "----------------------------------------------------"

cd akeneo

echo "Push Configs for Akeneo"
cp   ../ci/routing_splash.yml     config/routing/splash.yml
cp   ../ci/config_splash.yml      config/packages/splash.yml
cp    ../phpunit.xml.dist         phpunit.xml.dist

echo "Register Symfony Bundles"
cp   ../ci/bundles.test.php       config/bundles.test.php
php bin/console config:dump-reference

echo "Composer Require"
composer require splash/phpcore:dev-master splash/php-bundle:dev-master splash/akeneo-bundle:dev-master --no-interaction --prefer-dist --no-suggest

mkdir  -p ./src/Splash/Tests/Tools
cp    ../tests/KernelTestCase.php ./src/Splash/Tests/Tools/TestCase.php
cp    ../tests/KernelTestCase.php ./vendor/splash/phpcore/Tests/Tools/TestCase.php

echo "Splash Bundle Configuration"
php bin/console debug:config splash       --env=test