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
echo "--> AKENEO - Install PhpUnit 7.x"
echo "----------------------------------------------------"

curl -sSfL -o /usr/bin/phpunit https://phar.phpunit.de/phpunit-7.5.2.phar;
chmod -x 7777 /usr/bin/phpunit