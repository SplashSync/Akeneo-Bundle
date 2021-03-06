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

################################################################
# Force Failure if ONE line Fails
set -e

echo "----------------------------------------------------"
echo "--> AKENEO - Install Version $AKENEO_VERSION"
echo "----------------------------------------------------"

echo "Create Akeneo Community Project"
composer create-project akeneo/pim-community-dev akeneo $AKENEO_VERSION --prefer-dist

echo "Configuring Akeneo"
cp ci/oneup_flysystem.yml akeneo/config/packages/dev/oneup_flysystem.yml
cp ci/oneup_flysystem.yml akeneo/config/packages/prod/oneup_flysystem.yml
cp ci/oneup_flysystem.yml akeneo/config/packages/test/oneup_flysystem.yml

echo "Run Akeneo Installer"
cd akeneo
chmod -x bin/console
chmod 7777 bin/console

rm -Rf var/cache/*

#php bin/console pim:install               --env=prod  --force --symlink --clean --no-interaction --no-debug
php bin/console pim:install:db --env=prod --no-interaction --no-debug --catalog=src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev
php bin/console pim:installer:assets      --env=prod  --symlink --clean --no-interaction --no-debug