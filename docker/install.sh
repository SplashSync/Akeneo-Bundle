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

echo -e "\e[45m *********************************** \e[49m"
echo -e "\e[45m ** Build Akeneo Docker ...          \e[49m"
echo -e "\e[45m *********************************** \e[49m"

docker compose up -d

echo -e "\e[45m *********************************** \e[49m"
echo -e "\e[45m ** Install Akeneo ...               \e[49m"
echo -e "\e[45m *********************************** \e[49m"

echo -e "\e[45m ** Composer Update                  \e[49m"
docker compose exec fpm composer update
#docker compose exec fpm composer install

echo -e "\e[45m ** Yarn Install                     \e[49m"
docker compose run --rm node yarn install
docker compose run --rm node yarn upgrade

echo -e "\e[45m ** Akeneo Install                   \e[49m"
docker compose exec fpm chmod -x bin/console
docker compose exec fpm chmod 777 bin/console
docker compose exec fpm php bin/console --env=prod cache:clear --no-warmup
docker compose exec fpm php bin/console --env=prod pim:install:db \
              --catalog=vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal
#              --catalog=vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev
docker compose exec fpm php bin/console --env=prod pim:installer:assets --symlink --clean

echo -e "\e[45m ** Webpack Install                   \e[49m"
docker compose run --rm node yarn run webpack

echo -e "\e[45m ** Install Php Extensions ...       \e[49m"
docker compose exec fpm apt update
docker compose exec fpm apt install php-soap
docker compose restart fpm

