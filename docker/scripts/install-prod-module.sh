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
    echo -e "\e[45m ** Install Splash PROD Module        \e[49m"
    echo -e "\e[45m *********************************** \e[49m"
    ################################################################################
    echo "Configure Splash PROD Module via Composer"
    composer config extra.symfony.allow-contrib true
    composer config --unset repositories.splash
    ################################################################################
    echo "Install Splash PROD Module via Composer"
    composer require splash/akeneo-bundle --no-scripts --no-progress

    echo "YEP" > /home/module.installed.lock
else
    echo "SKIP >> Splash PROD Module Already Installed"
fi

echo "Install Splash PROD Module Config Files"
cp -Rf vendor/splash/akeneo-bundle/docker/config/*    /var/www/html/config/
cp -Rf vendor/splash/akeneo-bundle/tests/.env.test    /var/www/html/.env.test
cp -Rf vendor/splash/akeneo-bundle/phpunit.xml.dist   /var/www/html/phpunit.xml.dist

echo "LIST Splash Components Installed"
composer info | grep "splash"