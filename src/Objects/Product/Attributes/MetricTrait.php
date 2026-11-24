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

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractMetric as Metric;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as Product;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as Attribute;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Exception;
use Splash\Akeneo\Models\ExtraMetric;
use Splash\Core\SplashCore as Splash;

/**
 * Manage Metrics Types Attributes
 * Import / Export of Product Attribute Values
 */
trait MetricTrait
{
    /**
     * Akeneo Measures Manager
     *
     * @var MeasureManager
     */
    protected MeasureManager $measure;

    /**
     * Akeneo Measures Converter
     *
     * @var MeasureConverter
     */
    protected MeasureConverter $converter;

    /**
     * @var array
     */
    private static array $units = array(
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
     * @return null|float
     */
    protected function getMetricValue(Product $product, Attribute $attribute, string $isoLang, string $channel): ?float
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
        // No Value Defined
        if (null === $value) {
            return null;
        }

        return is_scalar($value) ? (float) $value : 0.0;
    }

    /**
     * DOUBLE - Read Attribute Data with Local & Scope Detection
     */
    protected function getMetricAsStringValue(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel
    ): ?string {
        //====================================================================//
        // Load Raw Attribute Value
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);
        //====================================================================//
        // Extract Generic Converted Value
        if ($value instanceof Metric) {
            return (string) sprintf('%.2F', $value->getData())." ".$this->getMetricSymbol($value, $isoLang);
        }

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * EXTRA METRIC - Read Attribute Data Converted to Configured Unit & Format
     *
     * @param Product     $product     Akeneo Product Object
     * @param Attribute   $attribute   Akeneo Attribute Object
     * @param ExtraMetric $extraMetric Extra Metric Definition
     * @param string      $isoLang
     * @param string      $channel
     *
     * @return null|float|int
     */
    protected function getMetricConvertedValue(
        Product $product,
        Attribute $attribute,
        ExtraMetric $extraMetric,
        string $isoLang,
        string $channel
    ): null|float|int {
        //====================================================================//
        // Load Raw Attribute Value
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);
        if (!$value instanceof Metric) {
            return null;
        }
        //====================================================================//
        // Empty Amount => No Value (Data may be Null despite Akeneo Typings)
        /** @var mixed $amount */
        $amount = $value->getData();
        if (!is_numeric($amount)) {
            return null;
        }

        //====================================================================//
        // Convert Value to Target Unit
        try {
            $converted = $this->converter
                ->setFamily($value->getFamily())
                ->convert($value->getUnit(), $extraMetric->unit, $amount)
            ;
        } catch (Exception $ex) {
            Splash::log()->war(sprintf(
                "Unable to convert %s to %s: %s",
                $attribute->getCode(),
                $extraMetric->unit,
                $ex->getMessage()
            ));

            return null;
        }

        return $extraMetric->format((float) $converted);
    }

    /**
     * EXTRA METRIC - Get Unit Symbol for Attribute Metric Family
     *
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $unit      Unit Code
     *
     * @return string Unit Symbol, or Unit Code if not Found
     */
    protected function getMetricUnitSymbol(Attribute $attribute, string $unit): string
    {
        try {
            $familySymbols = $this->measure->getUnitSymbolsForFamily($attribute->getMetricFamily());
        } catch (Exception $ex) {
            return $unit;
        }

        return is_scalar($familySymbols[$unit] ?? null) ? (string) $familySymbols[$unit] : $unit;
    }

    /**
     * EXTRA METRIC - Check if Unit Exists for Attribute Metric Family
     *
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $unit      Unit Code
     *
     * @return bool
     */
    protected function isMetricUnitAllowed(Attribute $attribute, string $unit): bool
    {
        try {
            return (bool) $this->measure->unitCodeExistsInFamily($unit, $attribute->getMetricFamily());
        } catch (Exception $ex) {
            return false;
        }
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
     * @throws Exception
     *
     * @return bool
     */
    protected function setMetricValue(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel,
        $data
    ): bool {
        //====================================================================//
        // Safety Check
        if (!is_scalar($data)) {
            return false;
        }
        //====================================================================//
        // Prepare Attribute Data
        $rawData = array(
            "amount" => (float) $data,
            "unit" => $this->getMetricUnit($attribute),
        );

        return $this->setCoreValue($product, $attribute, $isoLang, $channel, $rawData);
    }

    /**
     * DOUBLE - Try to Fetch metric Symbol from Measure Manager or Translations
     *
     * @param Metric $value   Akeneo Measure
     * @param string $isoLang Current Language ISO Code
     *
     * @return string
     */
    protected function getMetricSymbol(Metric $value, string $isoLang): string
    {
        //====================================================================//
        // Load Unit Symbol from Manager
        try {
            $familySymbols = $this->measure->getUnitSymbolsForFamily($value->getFamily());
            if (isset($familySymbols[$value->getUnit()])) {
                return (string) $familySymbols[$value->getUnit()];
            }
        } catch (\InvalidArgumentException $ex) {
            Splash::log()->war($ex->getMessage());
        }

        //====================================================================//
        // Try to Get Translated Unit Name
        return $this->locales->trans("pim_measure.units.".$value->getUnit(), array(), "messages", $isoLang);
    }

    /**
     * DOUBLE - Read Attribute Data with Local & Scope Detection
     *
     * @param Attribute $attribute Akeneo Attribute Object
     *
     * @throws Exception
     *
     * @return string
     */
    private function getMetricUnit(Attribute $attribute): string
    {
        $metricFamily = $attribute->getMetricFamily();
        //====================================================================//
        // Safety Check => Verify this Metric Type is Known
        if (!isset(self::$units[$metricFamily])) {
            throw new Exception(sprintf("Unknown metric family name: %s", $metricFamily));
        }

        return self::$units[$metricFamily];
    }
}
