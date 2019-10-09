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

namespace Splash\Akeneo\Objects\Product;

use Splash\Client\Splash;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Pim\Component\Catalog\Model\Product;

/**
 * Sylius Product Objects Lists
 */
trait ObjectsListTrait
{
    use \Splash\Bundle\Helpers\Doctrine\ObjectsListHelperTrait;

    /**
     * Transform Product To List Array Data
     *
     * @param Product $variant
     *
     * @return array
     */
    protected function getObjectListArray(Product $variant): array
    {
//        $product = $variant->getProduct();
//Splash::log()->www("pOr", $variant->getId());
//Splash::log()->www("pOr", get_class_methods(get_class($variant)));

//        $dateUpdated = $variant->getUpdated();
//            $variant->getLocale();
        return array(
            'id' => $variant->getId(),
            'identifier' => $variant->getIdentifier(),
            'enabled' => $variant->isEnabled(),
            'isVariant' => $variant->isVariant(),
            'label' => $variant->getLabel(),
            'updated' => $variant->getUpdated()->format(SPL_T_DATETIMECAST),
//            'id' => $variant->getId(),
            
//            'code' => $variant->getCode(),
//            'enabled' => $product ? $product->isEnabled() : false,
//            'email' => $variant->getName(),
//            'phoneNumber' => $variant->getName(),
//            'onHand' => $variant->getOnHand(),
        );
    }
}