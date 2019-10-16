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

echo "*************************************************************************"
echo "** Build Akeneo Docker ..."
echo "*************************************************************************"

docker-compose up -d

echo "*************************************************************************"
echo "** Install Akeneo ..."
echo "*************************************************************************"

echo "** Compsoer Update "
docker-compose exec fpm composer update
echo "** Yarn Install "
docker-compose run --rm node yarn install

echo "** Install Akeneo "
docker-compose exec fpm cp  /srv/pim/vendor/akeneo/pim-community-dev/app/PimRequirements.php /srv/pim/app/PimRequirements.php   
docker-compose exec fpm php bin/console --env=prod cache:clear --no-warmup
docker-compose exec fpm php bin/console --env=prod pim:install --force --symlink --clean
echo "** Install Webpack "
docker-compose run --rm node yarn run webpack

