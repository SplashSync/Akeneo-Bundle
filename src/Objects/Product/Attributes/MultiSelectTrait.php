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
use Splash\Models\Helpers\InlineHelper;

/**
 * Manage Multi-Select Types Attributes
 * Import / Export of Product Attribute Values
 */
trait MultiSelectTrait
{
    /**
     * MULTI-SELECT - Read Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     *
     * @return null|string
     */
    protected function getMultiSelectValue(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel
    ): ?string {
        //====================================================================//
        // Load Raw Attribute Value
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);
        $value = is_array($value) ? $value : array();

        return InlineHelper::fromArray($value);
    }

    /**
     * MULTI-SELECT - Read Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     *
     * @return null|string
     */
    protected function getMultiSelectTranslated(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel
    ): ?string {
        $translated = $this->getMultiSelectLabels($product, $attribute, $isoLang, $channel);

        return is_null($translated) ? null : InlineHelper::fromArray($translated);
    }

    /**
     * MULTI-SELECT - Read Attribute Translated Values as a Single String
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     * @param string    $separator Separator used to Join Values
     *
     * @return null|string
     */
    protected function getMultiSelectAsString(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel,
        string $separator
    ): ?string {
        $translated = $this->getMultiSelectLabels($product, $attribute, $isoLang, $channel, true);

        return is_null($translated) ? null : implode($separator, $translated);
    }

    /**
     * MULTI-SELECT - Read Attribute Options Translated Labels
     *
     * @param Product   $product      Akeneo Product Object
     * @param Attribute $attribute    Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     * @param bool      $codeFallback Use Option Code when Translation is Empty
     *
     * @return null|string[]
     */
    private function getMultiSelectLabels(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel,
        bool $codeFallback = false
    ): ?array {
        //====================================================================//
        // Load Raw Attribute Value
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);
        if (!is_array($value) || empty($value)) {
            return null;
        }
        //====================================================================//
        // Translate Attribute Options Value
        $translated = array();
        foreach ($value as $index => $valueCode) {
            $label = (string) $this->getOptionTranslation($attribute, $valueCode, $isoLang);
            $translated[$index] = ($codeFallback && ('' === $label)) ? (string) $valueCode : $label;
        }

        return $translated;
    }
}
