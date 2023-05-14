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
cd $INSTALL_DIR

################################################################################
# Clean Akeneo Image for Upload to Registry
echo -e "\e[45m *********************************** \e[49m"
echo -e "\e[45m ** Clean Akeneo Image for Upload    \e[49m"
echo -e "\e[45m *********************************** \e[49m"
rm -Rf node_modules
rm -Rf vendor/*