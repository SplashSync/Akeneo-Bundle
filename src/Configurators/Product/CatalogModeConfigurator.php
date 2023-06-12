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
 * Configure Products for Catalog Mode
 */
class CatalogModeConfigurator extends AbstractConfigurator
{
    /**
     * @inheritDoc
     */
    public function getConfiguration(): array
    {
        return array(
            "Product" => array(
                "fields" => array(
                    //====================================================================//
                    // Product Variants is Read Only
                    "id@variants" => array("write" => "0"),
                    //====================================================================//
                    // Product Attributes is Read Only
                    "code@attributes" => array("write" => "0"),
                    "label@attributes" => array("write" => "0"),
                    "value@attributes" => array("write" => "0"),
                    //====================================================================//
                    // Images Gallery is Read Only
                    "image@images" => array("write" => "0"),
                    "position@images" => array("write" => "0"),
                    "cover@images" => array("write" => "0"),
                    "visible@images" => array("write" => "0"),
                )
            )
        );
    }
}
