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

use Exception;
use Pim\Component\Catalog\Model\AttributeInterface as Attribute;
use Pim\Component\Catalog\Model\EntityWithValuesInterface as Product;
use Pim\Component\Catalog\Model\Metric;
use Splash\Core\SplashCore as Splash;

/**
 * Manage Metrics Types Attributes
 * Import / Export of Product Attribute Values
 */
trait MetricTrait
{
    /**
     * @var array
     */
    private static $units = array(
        "Weight" => "KILOGRAM",
        "Length" => "METER",
    );

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

        return (float) $value;
    }

    /**
     * DOUBLE - Write Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     * @param mixed     $data
     *
     * @return bool
     */
    protected function setMetricValue(Product $product, Attribute $attribute, string $isoLang, string $channel, $data): bool
    {
        $rawData = array(
            "amount" => (float) $data,
            "unit" => $this->getMetricUnit($attribute),
        );

        return $this->setCoreValue($product, $attribute, $isoLang, $channel, $rawData);
    }

    /**
     * DOUBLE - Read Attribute Data with Local & Scope Detection
     *
     * @param Attribute $attribute Akeneo Attribute Object
     *
     * @return string
     */
    private function getMetricUnit(Attribute $attribute): ?string
    {
        $metricFamily = $attribute->getMetricFamily();
        //====================================================================//
        // Safety Check => Verify this Metric Type is Known
        if (!isset(static::$units[$metricFamily])) {
            throw new Exception(sprintf("Unknown metric family name: %s", $metricFamily));
        }

        return static::$units[$metricFamily];
    }
}
