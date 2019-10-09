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

use Splash\Core\SplashCore as Splash;
use Pim\Component\Catalog\Model\AttributeInterface as Attribute;
use Pim\Component\Catalog\Model\EntityWithValuesInterface as Product;
use Pim\Component\Catalog\Model\ProductPrice as Price;
use Splash\Models\Objects\PricesTrait;
use Symfony\Component\Intl\Intl;

/**
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
        // Search for Currency Price in Collection
        /** @var Price $prdPrice */
        foreach ($value as $prdPrice) {
            if (strtolower($prdPrice->getCurrency()) == strtolower($this->getCurrency())) {
                return $this->buildPrice($prdPrice->getData());
            }
        }

        return $this->buildPrice(0);
    }

    /**
     *  @abstract    Write Attribute Data
     *
     *  @param  ProductInterface    $Object         Akeneo Product Object
     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
     *  @param  mixed               $Data           Field Input Splash Formated Data
     *
     *  @return bool
     */
    protected function importPrice(ProductInterface $Object, AttributeInterface $Attribute, $Data)
    {
        return $this->setAttributeValue($Object, $Attribute, array(array( "amount" => self::Prices()->TaxExcluded($Data), "currency" => $Data["code"] )));
    }

    /**
     * @param float $htPrice
     *
     * @return array
     */
    private function buildPrice(float $htPrice): array
    {
        $currency = $this->getCurrency();
        
        return self::Prices()->Encode(
            (double) $htPrice,
            (double) 0,
            null,
            $currency,
            Intl::getCurrencyBundle()->getCurrencySymbol($currency),
            Intl::getCurrencyBundle()->getCurrencyName($currency)
        );
    }
}
