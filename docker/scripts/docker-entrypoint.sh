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
# Check if Imagick is Installed
bash /builds/SplashSync/Akeneo-Bundle/docker/scripts/install-yarn.sh
################################################################################
# Check if Yarn is Installed
bash /builds/SplashSync/Akeneo-Bundle/docker/scripts/install-imagick.sh
################################################################################
# Check if Akeneo Core is Installed
bash /builds/SplashSync/Akeneo-Bundle/docker/scripts/install-akeneo-core.sh
################################################################################
# Check if Akeneo Assets are Installed
bash /builds/SplashSync/Akeneo-Bundle/docker/scripts/install-akeneo-assets.sh
################################################################################
# Check if Akeneo Fixtures are Installed
bash /builds/SplashSync/Akeneo-Bundle/docker/scripts/install-akeneo-fixtures.sh
################################################################################
# Check if Module is Installed
if [ $APP_ENV = "dev"  ]; then
  bash /builds/SplashSync/Akeneo-Bundle/docker/scripts/install-dev-module.sh
else
  bash /builds/SplashSync/Akeneo-Bundle/docker/scripts/install-prod-module.sh
fi
#################################################################################
echo "Configure Folders"
chown www-data:www-data -Rf /var/www/html/var
chown www-data:www-data -Rf /var/www/html/public
#################################################################################
echo "Serving App..."
exec apache2-foreground



