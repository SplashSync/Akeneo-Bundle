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

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Component\Catalog\Model\AttributeInterface as Attribute;
use Pim\Component\Catalog\Model\ProductInterface as Product;
use Splash\Core\SplashCore as Splash;

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
     * @return mixed
     */
    protected function getSelectValue(Product $product, Attribute $attribute, string $isoLang, string $channel)
    {
        //====================================================================//
        // Load Raw Attribute Value
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);

        if ($value instanceof AttributeOption) {
            return (string) $value->getCode();
        }

        return  (string) substr($value, 1, strlen($value) - 2);
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

        /** @var Collection $options */
        $options = $attribute->getOptions();
        if (is_iterable($options)) {
            foreach ($options as $option) {
                $code = (string) $option->getCode();
                $choices[$code] = $option->getOptionValues()->containsKey($isoLang)
                    ? $option->getOptionValues()->get($isoLang)->getValue()
                    : $option->getTranslation($isoLang)->getLabel();
            }
        }

        return $choices;
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
     * @return mixed
     */
    protected function setSelectValue(Product $product, Attribute $attribute, string $isoLang, string $channel, $data)
    {
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
        if (is_scalar($data) && empty($data)) {
            return $this->setCoreValue($product, $attribute, $isoLang, $channel, null);
        }

        return false;
    }
}
