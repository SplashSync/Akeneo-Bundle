<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Akeneo\Dictionary;

/**
 * Output Formats for Extra Metrics Virtual Fields
 */
class ExtraMetricFormats
{
    /**
     * Converted Value Rounded to an Integer
     *
     * @var string
     */
    const INT = "int";

    /**
     * Converted Value as a Float
     *
     * @var string
     */
    const DOUBLE = "double";

    /**
     * All Allowed Formats
     *
     * @var string[]
     */
    const ALL = array(self::INT, self::DOUBLE);

    /**
     * Splash Field Type for Each Format
     *
     * @var array<string, string>
     */
    const SPLASH_TYPES = array(
        self::INT => SPL_T_INT,
        self::DOUBLE => SPL_T_DOUBLE,
    );
}
