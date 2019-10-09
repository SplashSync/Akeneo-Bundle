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

/**
 * Access to Local Product Attributes Fields
 */
trait AttributesTrait
{

    /**
     * Build Fields using FieldFactory
     */
    public function buildAttributeFields()
    {
        $this->attr->build($this->fieldsFactory());
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    public function getAttributeFields(string $key, string $fieldName)
    {
        //====================================================================//
        // Safety Check => Verify if FieldName is An Attribute Type
        if(!$this->attr->has($fieldName)) {
            return;
        }
        //====================================================================//
        // Read Data from Attributes Service
        $this->out = array_merge_recursive(
            $this->out, 
            $this->attr->get($this->object, $fieldName)
        );
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setAttributeFields($fieldName, $fieldData)
    {
//        switch ($fieldName) {
//            //====================================================================//
//            // Variant Readings
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
//            case 'enabled':
//                $this->setGenericBool($fieldName, $fieldData);
//
//                break;
//            default:
//                return;
//        }
//        
//        unset($this->in[$fieldName]);
    }
}
