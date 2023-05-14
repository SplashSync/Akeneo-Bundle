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
# Check if Akeneo Fixtures is Installed
if [ ! -f /home/fixtures.installed.lock ]; then
    ################################################################################
    echo -e "\e[45m *********************************** \e[49m"
    echo -e "\e[45m ** Install Akeneo Db & Fixtures     \e[49m"
    echo -e "\e[45m *********************************** \e[49m"
    php bin/console pim:install:db --env=prod --no-interaction --no-debug --catalog=$AKENEO_FIXTURES
    echo "YEP" > /home/fixtures.installed.lock
else
    echo -e "\e[45m ** Akeneo Fixtures Already Here     \e[49m"
fi

