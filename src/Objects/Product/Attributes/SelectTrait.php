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
use Splash\Core\SplashCore as Splash;

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
        if (is_object($value)) {
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

        foreach ($attribute->getOptions() as $Option) {
            $code = (string) $Option->getCode();
            if ($Option->getOptionValues()->containsKey($isoLang)) {
                $choices[ $code ] = $Option->getOptionValues()->get($isoLang)->getValue();
            } else {
                $choices[ $code ] = $Option->getTranslation($isoLang)->getLabel();
            }
        }

        return $choices;
    }
    

    /**
     *  @abstract    Write Attribute Data with Local & Scope Detection
     *
     *  @param  ProductInterface    $Object         Akeneo Product Object
     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
     *  @param  mixed               $Data           Field Input Splash Formated Data
     *
     *  @return bool
     */
    protected function importOption(ProductInterface $Object, AttributeInterface $Attribute, $Data)
    {
        if (empty($Data)) {
            return;
        }

        return $this->setAttributeValue($Object, $Attribute, $Data);
    }    
}
