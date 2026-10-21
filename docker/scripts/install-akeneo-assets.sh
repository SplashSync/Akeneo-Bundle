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
# Check if Akeneo Assets is Installed
if [ ! -f public/dist/main.min.js ]; then
    echo -e "\e[45m *********************************** \e[49m"
    echo -e "\e[45m ** Install Akeneo Assets            \e[49m"
    echo -e "\e[45m *********************************** \e[49m"
    php bin/console pim:installer:assets                --env=prod  --symlink --clean --no-interaction --no-debug
    php bin/console pim:installer:dump-require-paths    --env=prod  --no-interaction --no-debug
    php bin/console assets:install                      --env=prod  --symlink --no-interaction --no-debug

    echo -e "\e[45m *********************************** \e[49m"
    echo -e "\e[45m ** Build Webpack Assets            \e[49m"
    echo -e "\e[45m *********************************** \e[49m"
    ################################################################################
    # Limit Webpack Memory Usage: CI runners have many CPUs but limited memory
    # Akeneo spawns one Terser & Thread-Loader worker per CPU and builds source maps
    WEBPACK_CONFIG=vendor/akeneo/pim-community-dev/webpack.config.js
    sed -i \
        -e "s/parallel: true,/parallel: ${WEBPACK_WORKERS:-2},/" \
        -e "s/devtool: 'source-map',/devtool: false,/" \
        -e "s/'thread-loader',/{loader: 'thread-loader', options: {workers: ${WEBPACK_WORKERS:-2}}},/" \
        $WEBPACK_CONFIG
    grep -nE "parallel:|devtool:|thread-loader" $WEBPACK_CONFIG
    ################################################################################
    yarn install
    yarn run less
    yarn run update-extensions
    yarn run packages:build
    yarn run webpack
else
    echo -e "\e[45m ** SKIP > Assets Already Installed  \e[49m"
fi
