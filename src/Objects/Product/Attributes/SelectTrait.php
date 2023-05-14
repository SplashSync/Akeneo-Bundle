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
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;

/**
 * Manage Select Types Attributes
 * Import / Export of Product Attribute Values
 */
trait SelectTrait
{
    /**
     * SELECT - Read Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     *
     * @return null|string
     */
    protected function getSelectValue(Product $product, Attribute $attribute, string $isoLang, string $channel): ?string
    {
        //====================================================================//
        // Load Raw Attribute Value
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);

        if ($value instanceof AttributeOption) {
            return (string) $value->getCode();
        }

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * SELECT - Read Attribute Translation with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     *
     * @return null|string
     */
    protected function getSelectValueTranslation(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel
    ): ?string {
        //====================================================================//
        // Load Raw Attribute Value
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);
        //====================================================================//
        // Translate Attribute Value
        if ($value instanceof AttributeOption) {
            return (string) $this->getOptionTranslation($attribute, (string) $value->getCode(), $isoLang);
        }
        if (is_string($value) && !empty($value)) {
            return (string) $this->getOptionTranslation($attribute, $value, $isoLang);
        }

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * SELECT - Read Attribute Possibles Choices
     *
     * @param Attribute $attribute
     * @param string    $isoLang
     *
     * @return array
     */
    protected function getSelectChoices(Attribute $attribute, string $isoLang): array
    {
        $choices = array();

        /** @var AttributeOption[] $options */
        $options = $attribute->getOptions();
        if (is_iterable($options)) {
            foreach ($options as $option) {
                $code = (string) $option->getCode();
                /** @var null|AttributeOptionValueInterface $value */
                $value = $option->getOptionValues()->get($isoLang);
                if ($value) {
                    $choices[$code] = $value->getValue();
                } else {
                    $translation = $option->setLocale($isoLang)->getTranslation();
                    $choices[$code] = $translation ? $translation->getValue() : null;
                }
            }
        }

        return $choices;
    }

    /**
     * SELECT - Translate Attribute Option Data with Local
     *
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $valueCode
     * @param string    $isoLang
     *
     * @return null|string
     */
    protected function getOptionTranslation(Attribute $attribute, string $valueCode, string $isoLang): ?string
    {
        /** @var AttributeOption[] $options */
        $options = $attribute->getOptions();
        //====================================================================//
        // Safety Check
        if (!is_iterable($options)) {
            return null;
        }
        //====================================================================//
        // Search Attribute Option by Code
        foreach ($options as $option) {
            if ($option->getCode() != $valueCode) {
                continue;
            }
            $translation = $option->setLocale($isoLang)->getTranslation();

            return $translation ? $translation->getValue() : null;
        }

        return null;
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
    protected function setSelectValue(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel,
        $data
    ): bool {
        //====================================================================//
        // Load Possible Select Values
        $choices = array_keys($this->getSelectChoices($attribute, $isoLang));
        //====================================================================//
        // Check Value is Part of Possible Values
        if (is_scalar($data) && in_array($data, $choices, true)) {
            return $this->setCoreValue($product, $attribute, $isoLang, $channel, $data);
        }
        //====================================================================//
        // Check Value is Empty
        if ((is_null($data) || is_scalar($data)) && empty($data)) {
            return $this->setCoreValue($product, $attribute, $isoLang, $channel, null);
        }

        return false;
    }
}
