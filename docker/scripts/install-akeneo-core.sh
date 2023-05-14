#!/bin/bash
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

################################################################################
# Check if Akeneo Project is Installed
if [ ! -f $INSTALL_DIR/composer.json ]; then
    ################################################################################
    # Install Akeneo Standard Project
    echo -e "\e[45m *********************************** \e[49m"
    echo -e "\e[45m ** Create Akeneo $AKENEO_VERSION    \e[49m"
    echo -e "\e[45m *********************************** \e[49m"
    composer create-project akeneo/pim-community-standard /var/www/html $AKENEO_VERSION --prefer-dist
else
    echo -e "\e[45m ** SKIP > Project Already Here      \e[49m"
fi

################################################################################
# Check if Akeneo Vendor is Populated
if [ ! -f $INSTALL_DIR/vendor/autoload.php ]; then
    ################################################################################
    echo -e "\e[45m *********************************** \e[49m"
    echo -e "\e[45m ** Install Akeneo Dependencies      \e[49m"
    echo -e "\e[45m *********************************** \e[49m"
    composer install
else
    echo -e "\e[45m ** SKIP > Dependencies Already Here \e[49m"
fi
