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

namespace Splash\Akeneo\Objects\Product\Attributes;

use Pim\Component\Catalog\Model\AttributeInterface as Attribute;
use Pim\Component\Catalog\Model\EntityWithValuesInterface as Product;
//use Pim\Component\Catalog\Model\ProductInterface as Product;
//use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Updater\PropertySetter;
use Splash\Core\SplashCore as Splash;

/**
 * Import / Export of Product Attribute Values
 */
trait CoreTrait
{
    /**
     * @var PropertySetter
     */
    protected $setter;

    /**
     * CORE - Read Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     *
     * @return mixed
     */
    protected function getCoreValue(Product $product, Attribute $attribute, string $isoLang, string $channel)
    {
        //====================================================================//
        // Get Attribute Code
        $code = $attribute->getCode();
        //====================================================================//
        // Check if Attribute is Used for this Product
        if (!in_array($code, $product->getUsedAttributeCodes(), true)) {
            //====================================================================//
            // Load Value from Parent Product
            $parent = $product->getParent();
            if ($parent instanceof Product) {
                return $this->getCoreValue($parent, $attribute, $isoLang, $channel);
            }

            return null;
        }
        //====================================================================//
        // Load Product Value Object
        $value = $product->getValue(
            $code,
            $attribute->isLocalizable() ? $isoLang : null,
            $attribute->isScopable() ? $channel : null
        );
        if (null == $value) {
            return null;
        }

        //====================================================================//
        // Return Raw Product Value Data
        return $value->getData();
    }

    /**
     * CORE - Write Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     * @param mixed     $data
     *
     * @return mixed
     */
    protected function setCoreValue(Product $product, Attribute $attribute, string $isoLang, string $channel, $data)
    {
        //====================================================================//
        // Get Attribute Code
        $code = $attribute->getCode();

        //====================================================================//
        // Prepare Setter Options
        $options = array(
            "locale" => $attribute->isLocalizable() ? $isoLang : null,
            "scope" => $attribute->isScopable() ? $channel : null
        );

        //====================================================================//
        // Update Product Using Property Setter
        $this->setter->setData($product, $code, $data, $options);

        return true;
    }
}
