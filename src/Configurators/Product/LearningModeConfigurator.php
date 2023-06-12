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

namespace Splash\Akeneo\Configurators\Product;

use Splash\Models\AbstractConfigurator;

/**
 * Configure Products for Learning Mode
 */
class LearningModeConfigurator extends AbstractConfigurator
{
    /**
     * @inheritDoc
     */
    public function getConfiguration(): array
    {
        return array(
            "Product" => array(
                //====================================================================//
                // Fields Configuration
                "fields" => array(
                    //====================================================================//
                    // Product Variants Prefer Write
                    "id@variants" => array("syncmode" => "import"),
                    //====================================================================//
                    // Product Attributes Prefer Write
                    "code@attributes" => array("syncmode" => "import"),
                    "label@attributes" => array("syncmode" => "import"),
                    "value@attributes" => array("syncmode" => "import"),
                    //====================================================================//
                    // Images Gallery Prefer Write
                    "image@images" => array("syncmode" => "import"),
                    "position@images" => array("syncmode" => "import"),
                    "cover@images" => array("syncmode" => "import"),
                    "visible@images" => array("syncmode" => "import"),
                )
            )
        );
    }
}
