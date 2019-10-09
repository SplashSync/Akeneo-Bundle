
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
#!/bin/sh

echo "\n* Install Php Soap Extension..."

# apt-get update && apt-get install -y php7.2-soap libmcrypt-dev  php-pecl
# pecl install mcrypt-1.0.1
# docker-php-ext-install soap mcrypt

echo "\n* Download Akeneo"

if [ ! -f composer.json ]; then

	git clone  --depth=1 --branch="$AKENEO_VERSION" https://github.com/akeneo/pim-community-standard.git /home/docker/git-src/
	cp -Rf /home/docker/git-src/app ./app
	cp -Rf /home/docker/git-src/bin ./bin
	cp -Rf /home/docker/git-src/var ./var
	cp -Rf /home/docker/git-src/web ./web
	cp -Rf /home/docker/git-src/composer.json ./composer.json
	cp -Rf /home/docker/git-src/package.json  ./package.json
	chown -Rf docker:docker ./web/bundles
	chmod -Rf 775 ./web/bundles

fi

echo "\n* Configuring Akeneo"
cp /home/docker/src/docker/parameters.yml app/config/parameters.yml


echo "\n* Install Akeneo Composer "
if [ ! -f composer.lock ]; then

	composer update --prefer-dist --no-suggest 
	# composer require akeneo/pim-community-dev $AKENEO_VERSION --prefer-dist --no-suggest  

fi


php bin/console --env=prod cache:clear --no-warmup
rm -rf var/cache/*

php bin/console --env=prod pim:install --force --symlink --clean
# $ docker-compose exec fpm bin/console --env=behat pim:installer:db          # Run this command only if you want to run behat or integration tests

# $ docker-compose run --rm node yarn run webpack

# cp -Rf /home/docker/src/app/* app/
# cp -Rf /home/docker/src/composer.json ./composer.json

# ls -l ./
# ls -l app/


# ls -l app/config


# echo "Configuring Akeneo"
# cp    vendor/akeneo/pim-community-dev/app/config/routing.yml 	app/config/routing.yml
# cp    vendor/akeneo/pim-community-dev/app/PimRequirements.php 	app/PimRequirements.php
# cat   /home/docker/src/build/routing_splash.yml   >> app/config/routing.yml
# echo  "" > vendor/akeneo/pim-community-dev/src/Pim/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev/products.csv
    
# echo "Install Akeneo"
# php bin/console pim:installer:assets  --symlink -vvv
# php bin/console pim:installer:assets  --symlink --clean --no-interaction --no-debug
# # php bin/console pim:install --force   --symlink --clean --env=test --no-interaction --no-debug
# # php bin/console debug:config splash --env=test --no-interaction --no-debug

# echo "Start Web Srever"
# php bin/console server:run 





# if [ ! -f /usr/local/bin/wp  ]; then

# 	echo "\n* Install Wordpress CLI ..."

# 	curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
# 	chmod +x wp-cli.phar
# 	mv wp-cli.phar /usr/local/bin/wp

# fi

# if [ ! -f wp-config.php ]; then

# 	echo "\n* Download Wordpress Core..."

# 	wp core download --allow-root --version=$WORDPRESS_VERSION 

# 	echo "\n* Configure Wordpress Core..."

# 	wp config create --allow-root --dbhost=$WORDPRESS_DB_HOST --dbname=$WORDPRESS_DB_NAME --dbuser=$WORDPRESS_DB_USER --dbpass=$WORDPRESS_DB_PASSWORD --dbprefix=$WORDPRESS_TABLE_PREFIX --skip-check

# 	echo "\n* Install Wordpress Core..."
# 	wp core install --allow-root --url=$WORDPRESS_URL --title="WP-SPLASH" --admin_user=admin --admin_password=$ADMIN_PASSWD --admin_email=$ADMIN_MAIL 


# 	echo "\n* Install WooCommerce Plugin ..."

# 	wp plugin install woocommerce --allow-root --activate
# 	wp option update woocommerce_currency EUR --allow-root

# 	echo "\n* Install Wordpress Additionnal Plugins ..."
# 	wp plugin install wp-multilang --allow-root --activate

# 	echo "\n* Install Wordpress for Splash ..."
# 	wp plugin activate splash-connector --allow-root
# 	wp option update splash_ws_id $SPLASH_WS_ID --allow-root
# 	wp option update splash_ws_key $SPLASH_WS_KEY --allow-root
# 	wp option update splash_ws_protocol SOAP --allow-root
# 	wp option update splash_advanced_mode "on" --allow-root
# 	wp option update splash_server_url $SPLASH_WS_HOST --allow-root
# 	wp option update splash_ws_user 1 --allow-root

# fi

# echo "\n* Install Php Soap Extension..."

# apt-get update && apt-get install -y libxml2-dev
# docker-php-ext-install soap

# echo "\n* Almost ! Starting web server now\n";
# exec apache2-foreground