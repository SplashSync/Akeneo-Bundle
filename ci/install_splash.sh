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

################################################################
# Force Failure if ONE line Fails
set -e

echo "----------------------------------------------------"
echo "--> AKENEO - Install Splash Bundle"
echo "----------------------------------------------------"

cd akeneo

echo "Push Configs for Akeneo"
cp   ../ci/routing_splash.yml     config/routes/splash.yml
cp   ../ci/config_splash.yml      config/packages/splash.yml
cp    ../phpunit.xml.dist         phpunit.xml.dist

echo "Register Symfony Bundles"
cp   ../ci/bundles.test.php       config/bundles.test.php

echo "Composer Require"
composer require splash/akeneo-bundle:5.0.x-dev --no-interaction --prefer-dist --no-scripts --no-plugins
cp  -Rf ../src/       vendor/splash/akeneo-bundle/src/
ls  -l vendor/splash/akeneo-bundle/src/

mkdir  -p ./src/Splash/Tests/Tools
cp    ../tests/KernelTestCase.php ./src/Splash/Tests/Tools/TestCase.php
cp    ../tests/KernelTestCase.php ./vendor/splash/phpcore/Tests/Tools/TestCase.php

echo "Splash Bundle Configuration"
rm -Rf var/cache/*
php bin/console config:dump-reference
php bin/console debug:config splash       --env=test