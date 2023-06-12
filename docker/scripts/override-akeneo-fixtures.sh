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

################################################################################
# Check if Akeneo Fixtures are Modified
if [ ! -f /home/fixtures.overrides.lock ]; then
    ################################################################################
    echo -e "\e[45m *********************************** \e[49m"
    echo -e "\e[45m ** Override Akeneo Fixtures         \e[49m"
    echo -e "\e[45m *********************************** \e[49m"
    # Walk on Cvs Files
    for FILE in /builds/SplashSync/Akeneo-Bundle/src/Resources/fixtures/overrides/*.*;
    do
      # Merge Contents with Original Values
      cat $FILE | awk '!a[$0]++' >> $AKENEO_FIXTURES/$(basename $FILE);
      echo -e "- Updated $AKENEO_FIXTURES/$(basename $FILE)"
    done

    # Clean Products List
    echo "" > $AKENEO_FIXTURES/products.csv;
    echo "" > $AKENEO_FIXTURES/product_models.csv;

    echo "YEP" > /home/fixtures.overrides.lock
else
    echo -e "\e[45m ** Akeneo Fixtures Already Updated  \e[49m"
fi

