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
use DateTime;

/**
 * Manage Date Types Attributes
 * Import / Export of Product Attribute Values
 */
trait DatesTrait
{
    /**
     * DATE - Read Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     *
     * @return null|string
     */
    protected function getDateValue(Product $product, Attribute $attribute, string $isoLang, string $channel): ?string
    {
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);
        if ($value instanceof DateTime) {
            return $value->format(SPL_T_DATECAST);
        }

        return null;
    }
}
