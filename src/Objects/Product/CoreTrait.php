<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Akeneo\Objects\Product;

use Splash\Core\SplashCore as Splash;

trait CoreTrait
{
    //====================================================================//
    // PRODUCT CORE INFOS
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     */
    public function buildCoreFields()
    {
        //====================================================================//
        // Product SKU
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("identifier")
            ->Name("Product SKU")
            ->MicroData("http://schema.org/Product", "model")
            ->isListed()
            ->isReadOnly();

        //====================================================================//
        // Active => Product Is Enables & Visible
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("enabled")
            ->Name("Enabled")
            ->MicroData("http://schema.org/Product", "active")
            ->isListed();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    public function getCoreFields(string $key, string $fieldName)
    {
        switch ($fieldName) {
            //====================================================================//
            // Variant Readings
            case 'identifier':
                $this->getGeneric($fieldName);

                break;
            case 'enabled':
                $this->getGenericBool($fieldName);

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setCoreFields($fieldName, $fieldData)
    {
        switch ($fieldName) {
            //====================================================================//
            // Variant Readings
//            case 'identifier':
//Splash::Log()->www("data", get_class($this->setter));                
//                
////                $this->setGeneric($fieldName, $fieldData);
//                $this->setter->setData($this->object, "sku", $fieldData, array(
//                    "locale" => null,
////                    "scope" => "ecommerce",
//                    "scope" => null,
//                ));
//                break;
            case 'enabled':
                $this->setGenericBool($fieldName, $fieldData);

                break;
            default:
                return;
        }
        
        unset($this->in[$fieldName]);
    }
}
