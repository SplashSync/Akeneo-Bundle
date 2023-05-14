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

################################################################################
# Check if ext-imagick is Installed
if [ ! -f /home/imagick.installed.lock ]; then
    apt-get update && apt-get install -y libmagickwand-dev --no-install-recommends && rm -rf /var/lib/apt/lists/*
    printf "\n" | pecl install imagick
    docker-php-ext-enable imagick
    echo "YEP" > /home/imagick.installed.lock
else
    echo "SKIP >> Php Imagick for Ubuntu Already Installed"
fi