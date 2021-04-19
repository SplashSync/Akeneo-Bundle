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
cat   ../ci/routing_splash.yml    >> app/config/routing.yml
cat   ../ci/config_splash.yml     >> app/config/config.yml
cp    ../phpunit.xml.dist phpunit.xml.dist

echo "Register Symfony Bundles"
sed -i 's|// your app bundles should be registered here|new \\Splash\\Bundle\\SplashBundle(), new Splash\\Akeneo\\SplashAkeneoBundle(),|g' app/AppKernel.php
#cat app/AppKernel.php

echo "Composer Require"
composer require splash/akeneo-bundle:3.0.x-dev --no-interaction --prefer-dist --no-suggest

mkdir  -p ./src/Splash/Tests/Tools
cp    ../tests/KernelTestCase.php ./src/Splash/Tests/Tools/TestCase.php
cp    ../tests/KernelTestCase.php ./vendor/splash/phpcore/Tests/Tools/TestCase.php

echo "Splash Bundle Configuration"
php bin/console debug:config splash       --env=test