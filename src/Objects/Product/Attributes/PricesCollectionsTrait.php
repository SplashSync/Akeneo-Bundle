<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice as Price;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as Attribute;
use Exception;
use Splash\Core\SplashCore as Splash;
use Splash\Models\Objects\PricesTrait;
use Symfony\Component\Intl\Intl;

/**
 * Manage Prices Types Attributes
 * Import / Export of Product Attribute Values
 */
trait PricesCollectionsTrait
{
    use PricesTrait;

    /**
     * PRICE - Read Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     *
     * @return mixed
     */
    protected function getPriceValue(Product $product, Attribute $attribute, string $isoLang, string $channel)
    {
        //====================================================================//
        // Load Raw Attribute Value
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);
        if (!is_iterable($value)) {
            return $this->buildPrice(0);
        }
        //====================================================================//
        // Load Raw VAT Attribute Value (if Exists)
        $vatValue = null;
        $vatCode = $attribute->getCode()."_vat";
        if ($this->hasForLocale($vatCode, $isoLang)) {
            $vatValue = $this->getCoreValue($product, $this->getByCode($vatCode), $isoLang, $channel);
        }

        //====================================================================//
        // Search for Currency Price in Collection
        /** @var Price $prdPrice */
        foreach ($value as $prdPrice) {
            if (strtolower($prdPrice->getCurrency()) == strtolower($this->getCurrency())) {
                return $this->buildPrice((float) $prdPrice->getData(), $vatValue);
            }
        }

        return $this->buildPrice(0);
    }

    /**
     * PRICE - Write Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     * @param mixed     $data
     *
     * @return mixed
     */
    protected function setPriceValue(Product $product, Attribute $attribute, string $isoLang, string $channel, $data)
    {
        $rawData = array(
            array(
                "amount" => self::Prices()->TaxExcluded($data),
                "currency" => $this->getCurrency(),
            ),
        );

        //====================================================================//
        // Update Raw VAT Attribute Value (if Exists)
        $vatCode = $attribute->getCode()."_vat";
        if ($this->hasForLocale($vatCode, $isoLang)) {
            $this->setCoreValue(
                $product,
                $this->getByCode($vatCode),
                $isoLang,
                $channel,
                self::Prices()->taxPercent($data)
            );
        }

        return $this->setCoreValue($product, $attribute, $isoLang, $channel, $rawData);
    }

    /**
     * Check if an Attribute Code is A Price VAT Field
     *
     * @param Attribute $attribute
     *
     * @return bool
     */
    protected function isPriceVatField(Attribute $attribute): bool
    {
        //====================================================================//
        // Attribute is a Number
        if (AttributeTypes::NUMBER != $attribute->getType()) {
            return false;
        }
        $code = $attribute->getCode();
        //====================================================================//
        // Attribute Code Ends with _vat
        if (strpos($code, "_vat") !== (strlen($code) - 4)) {
            return false;
        }

        return true;
    }

    /**
     * Build Splash Price Array
     *
     * @param float      $htPrice
     * @param null|float $vat
     *
     * @throws Exception
     *
     * @return array
     */
    private function buildPrice(float $htPrice, float $vat = null): array
    {
        $currency = $this->getCurrency();

        $price = self::Prices()->Encode(
            $htPrice,
            ($vat ? $vat : 0.0),
            null,
            $currency,
            (string) Intl::getCurrencyBundle()->getCurrencySymbol($currency),
            (string) Intl::getCurrencyBundle()->getCurrencyName($currency)
        );
        if (is_string($price)) {
            throw new Exception($price);
        }

        return $price;
    }
}
