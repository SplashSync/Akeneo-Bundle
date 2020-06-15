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

docker-compose up -d

echo -e "\e[45m *********************************** \e[49m"
echo -e "\e[45m ** Install Akeneo ...               \e[49m"
echo -e "\e[45m *********************************** \e[49m"

echo -e "\e[45m ** Composer Update                  \e[49m"
docker-compose exec fpm composer update
#docker-compose exec fpm composer install

echo -e "\e[45m ** Yarn Install                     \e[49m"
docker-compose run --rm node yarn install

echo -e "\e[45m ** Akeneo Install                   \e[49m"
docker-compose exec fpm chmod 777 bin/console
docker-compose exec fpm cp  /srv/pim/vendor/akeneo/pim-community-dev/app/PimRequirements.php /srv/pim/app/PimRequirements.php   
docker-compose exec fpm php bin/console --env=prod cache:clear --no-warmup
docker-compose exec fpm php bin/console --env=prod pim:install --force --symlink --clean
docker-compose exec fpm php bin/console --env=prod pim:installer:assets --symlink --clean

echo -e "\e[45m ** Webpack Install                   \e[49m"
docker-compose run --rm node yarn run webpack

echo -e "\e[45m ** Install PhpUnit 7.5 ...       \e[49m"
docker-compose exec fpm sudo curl -sSfL -o vendor/bin/phpunit https://phar.phpunit.de/phpunit-7.5.2.phar;

echo -e "\e[45m ** Install Php Extensions ...       \e[49m"
docker-compose exec fpm sudo apt update
docker-compose exec fpm sudo apt install php7.2-soap
docker-compose restart fpm

