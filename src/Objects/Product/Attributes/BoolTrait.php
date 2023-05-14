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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as Product;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as Attribute;

/**
 * Manage Bool Types Attributes
 * Import / Export of Product Attribute Values
 */
trait BoolTrait
{
    /**
     * BOOL - Read Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     *
     * @return bool
     */
    protected function isBoolValue(Product $product, Attribute $attribute, string $isoLang, string $channel): bool
    {
        return (bool) $this->getCoreValue($product, $attribute, $isoLang, $channel);
    }

    /**
     * BOOL - Read Attribute Data with Local & Scope Detection
     *
     * @param Product   $product       Akeneo Product Object
     * @param Attribute $attribute     Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     * @param bool      $attributeMode
     *
     * @return null|string
     */
    protected function getBoolAsStringValue(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel,
        bool $attributeMode
    ) {
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);
        if (is_null($value)) {
            return null;
        }
        if ($value) {
            return $this->locales->trans('Yes', array(), "messages", $isoLang);
        }

        return $attributeMode ? $this->locales->trans('No', array(), "messages", $isoLang) : null;
    }

    /**
     * BOOL - Write Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     * @param mixed     $data
     *
     * @return bool
     */
    protected function setBoolValue(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel,
        $data
    ): bool {
        return $this->setCoreValue($product, $attribute, $isoLang, $channel, (bool) $data);
    }
}
