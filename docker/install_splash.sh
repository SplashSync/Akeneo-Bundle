#!/bin/bash

echo "*************************************************************************"
echo "** Install Splash Bundle on Akeneo 2.0 ..."
echo "*************************************************************************"

# Create Folder Structure
if [ ! -d "./pim/2.0/src/Splash" ]; then
	mkdir "./pim/2.0/src/Splash"
fi

if [ ! -d "./pim/2.0/src/Splash/Akeneo" ]; then
	mkdir "./pim/2.0/src/Splash/Akeneo"
# 	ln -s   $( realpath ./src/ ) $( realpath ./pim/2.0/src/Splash/Akeneo/ )
fi

# Copy Splash Bundle
cp -Rf  $( realpath ./src/* ) $( realpath ./pim/2.0/src/Splash/Akeneo )

# Override App kernel
cp -Rf ./app/AppKernel.php ./pim/2.0/app/AppKernel.php

# Composer Update
sed -i -e 's/"minimum-stability": "stable"/"minimum-stability": "dev"/g' ./pim/2.0/composer.json >/dev/null 2>&1
docker-compose exec fpm composer require splash/php-bundle dev-master


