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
use Pim\Component\Catalog\Model\ProductInterface as Product;

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
     * @return mixed
     */
    protected function getBoolValue(Product $product, Attribute $attribute, string $isoLang, string $channel)
    {
        return (bool) $this->getCoreValue($product, $attribute, $isoLang, $channel);
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
    protected function setBoolValue(Product $product, Attribute $attribute, string $isoLang, string $channel, $data): bool
    {
        return $this->setCoreValue($product, $attribute, $isoLang, $channel, (bool) $data);
    }
}
