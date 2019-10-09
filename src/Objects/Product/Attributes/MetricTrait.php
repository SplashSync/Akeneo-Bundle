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
use Pim\Component\Catalog\Model\Metric;
use Splash\Core\SplashCore as Splash;

/**
 * Import / Export of Product Attribute Values
 */
trait MetricTrait
{
    /**
     * DOUBLE - Read Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     *
     * @return float
     */
    protected function getMetricValue(Product $product, Attribute $attribute, string $isoLang, string $channel): float
    {
        //====================================================================//
        // Load Raw Attribute Value
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);
        //====================================================================//
        // Extract Generic Converted Value
        if ($value instanceof Metric) {
            return (float) $value->getBaseData();
        }
        //====================================================================//
        // Extract Basoc Value
        if (is_float($value)) {
            return $value;
        }

        return 0.0;
    }
}
