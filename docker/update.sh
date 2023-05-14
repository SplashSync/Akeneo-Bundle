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

echo -e "\e[45m *********************************** \e[49m"
echo -e "\e[45m ** Build Akeneo V7 Docker Image     \e[49m"
echo -e "\e[45m *********************************** \e[49m"

echo "Build & Upload Docker Image"
docker build -t registry.gitlab.com/splashsync/akeneo-bundle:7.0 docker -f docker/akeneo-v7-php-8.1.Dockerfile
docker push registry.gitlab.com/splashsync/akeneo-bundle:7.0
