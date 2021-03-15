#!/bin/sh
################################################################################
#
# Copyright (C) 2021 BadPixxel <www.badpixxel.com>
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#
################################################################################

echo "----------------------------------------------------"
echo "--> AKENEO - Install Version $AKENEO_VERSION"
echo "----------------------------------------------------"

echo "Create Akeneo Community Project"
composer self-update --1
composer create-project akeneo/pim-community-dev akeneo $AKENEO_VERSION --prefer-dist --no-plugins

echo "Configuring Akeneo"
cp ci/parameters.yml.dist akeneo/app/config/parameters.yml
cp ci/parameters.yml.dist akeneo/app/config/parameters_test.yml

echo "Run Akeneo Installer"
cd akeneo
chmod -x 7777 bin/console
php bin/console pim:install               --env=prod  --force --symlink --clean --no-interaction --no-debug
php bin/console pim:installer:assets      --env=prod  --symlink --clean --no-interaction --no-debug