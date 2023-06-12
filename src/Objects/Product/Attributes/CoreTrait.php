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

namespace Splash\Akeneo\Objects\Product\Attributes;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractMetric;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface as ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertySetter;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use DateTime;
use Splash\Client\Splash;

/**
 * Manage Raw Types Attributes I/O
 * Import / Export of Product Attribute Values
 */
trait CoreTrait
{
    /**
     * @var PropertySetter
     */
    protected PropertySetter $setter;

    /**
     * CORE - Read Attribute Data with Local & Scope Detection
     *
     * @param Product|ProductModel $product   Akeneo Product Object
     * @param Attribute            $attribute Akeneo Attribute Object
     * @param string               $isoLang
     * @param string               $channel
     *
     * @return null|AbstractMetric|array|AttributeOption|DateTime|FileInfo|scalar
     */
    protected function getCoreValue($product, Attribute $attribute, string $isoLang, string $channel)
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
            if ($parent) {
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
        /** @phpstan-ignore-next-line */
        return $value->getData();
    }

    /**
     * CORE - Read Attribute Data with Local & Scope Detection
     *
     * @param Product|ProductModel $product   Akeneo Product Object
     * @param Attribute            $attribute Akeneo Attribute Object
     * @param string               $isoLang
     * @param string               $channel
     *
     * @return null|scalar
     */
    protected function getScalarValue($product, Attribute $attribute, string $isoLang, string $channel)
    {
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);

        return is_scalar($value) ? $value : null;
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
     * @return bool
     */
    protected function setCoreValue(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel,
        $data
    ): bool {
        //====================================================================//
        // Get Attribute Code
        $code = $attribute->getCode();

        //====================================================================//
        // Prepare Setter Options
        $options = array(
            "locale" => $attribute->isLocalizable() ? $isoLang : null,
            "scope" => $attribute->isScopable() ? $channel : null,
        );
        //====================================================================//
        // Get product Family Variant
        $familyVariant = $product->getFamilyVariant();
        if (!$familyVariant || Splash::isTravisMode()) {
            //====================================================================//
            // Update Product Using Property Setter
            $this->setter->setData($product, $code, $data, $options);

            return true;
        }
        //====================================================================//
        // Get Level For Field
        try {
            $attrLevel = $familyVariant->getLevelForAttributeCode($code);
        } catch (\InvalidArgumentException) {
            //====================================================================//
            // Field Does Not Exists for Variation
            return true;
        }

        while ($product) {
            //====================================================================//
            // Check Product level
            if ($product->getVariationLevel() == $attrLevel) {
                $this->setter->setData($product, $code, $data, $options);

                return true;
            }
            //====================================================================//
            // LOAD PARENT
            $product = $product->getParent();
        }

        return false;
    }
}
