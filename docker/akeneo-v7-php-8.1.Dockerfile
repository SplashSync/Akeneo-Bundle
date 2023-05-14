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


FROM registry.gitlab.com/badpixxel-projects/php-sdk:php-8.1

LABEL description="Akeneo V7 with PHP 8.1 for SplashSync"

################################################################################
# Declare Env Variables
################################################################################
# Conposer Config
ENV COMPOSER_MEMORY_LIMIT   -1
ENV COMPOSER_HOME           /var/www/.composer/
# Node JS Configs
ENV NODEJS_VERSION          18
ENV YARN_VERSION            "any"
# Akeneor App Config
ENV AKENEO_VERSION          ^7.0
ENV AKENEO_FIXTURES         src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev
ENV APP_ENV                 dev
ENV APP_DEBUG               1
# Directories
ENV INSTALL_DIR             /var/www/html
ENV MODULE_DIR              /builds/SplashSync/Akeneo-Bundle

################################################################################
# COPY Configs, Scripts, Etc...
################################################################################
COPY ./scripts/ /usr/local/bin
RUN chmod +x /usr/local/bin/*.sh

################################################################################
# Install Libs
################################################################################
RUN   install-imagick.sh
RUN   install-yarn.sh
RUN   install-akeneo-core.sh \
        && install-akeneo-assets.sh \
        && clean.sh
#RUN     install-prod-module.sh

################################################################################
# Configure PHP & Apache
################################################################################
RUN     a2enmod rewrite
COPY    ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf

################################################################################
# Configure rUN cOMMANDE
CMD     docker-entrypoint.sh

