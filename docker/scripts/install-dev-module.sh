#!/bin/sh
################################################################################
#
#  This file is part of SplashSync Project.
#
#  Copyright (C) Splash Sync <www.splashsync.com>
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
#
#  @author Bernard Paquier <contact@splashsync.com>
#
################################################################################

set -e
cd $INSTALL_DIR

if [ ! -f /home/module.installed.lock ]; then
    echo -e "\e[45m *********************************** \e[49m"
    echo -e "\e[45m ** Install Splash DEV Module        \e[49m"
    echo -e "\e[45m *********************************** \e[49m"
    ################################################################################
    echo "Install Symfony Flex & Phpunit"
    composer require symfony/flex symfony/browser-kit phpunit/phpunit:^9.0 --no-scripts --no-progress
    ################################################################################
    echo "Configure Splash DEV Module via Composer"
    composer config extra.symfony.allow-contrib true
    composer config repositories.splash '{ "type": "path", "url": "/builds/SplashSync/Akeneo-Bundle", "options": { "symlink": true, "versions": { "splash/akeneo-bundle": "dev-local" }}}'
    composer config minimum-stability dev
    ################################################################################
    echo "Install Splash DEV Module via Composer"
    composer require splash/akeneo-bundle:dev-local --no-scripts --no-progress

    echo "YEP" > /home/module.installed.lock
else
    echo "SKIP >> Splash DEV Module Already Installed"
fi

echo "Install Splash DEV Module Config Files"
cp -Rf /builds/SplashSync/Akeneo-Bundle/docker/config/* /var/www/html/config/
cp -Rf /builds/SplashSync/Akeneo-Bundle/tests/.env.test /var/www/html/.env.test
cp -Rf /builds/SplashSync/Akeneo-Bundle/phpunit.xml.dist /var/www/html/phpunit.xml.dist

echo "LIST Splash Components Installed"
composer info | grep "splash"