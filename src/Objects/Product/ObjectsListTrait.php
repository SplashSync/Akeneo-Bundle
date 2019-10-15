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

use Pim\Component\Catalog\Model\Product;
use Splash\Client\Splash;

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
        return array(
            'id' => $variant->getId(),
            'identifier' => $variant->getIdentifier(),
            'enabled' => $variant->isEnabled(),
            'variant' => $variant->isVariant(),
            'label' => $variant->getLabel(),
            'updated' => $variant->getUpdated()->format(SPL_T_DATETIMECAST),
        );
    }
}
