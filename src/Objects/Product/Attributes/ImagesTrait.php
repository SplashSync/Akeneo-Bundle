<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
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
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

/**
 * Manage Images Types Attributes
 * Import / Export of Product Attribute Values
 */
trait ImagesTrait
{
    /**
     * IMAGE - Read Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     *
     * @return mixed
     */
    protected function getImageValue(Product $product, Attribute $attribute, string $isoLang, string $channel)
    {
        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);

        if ($value instanceof FileInfo) {
            return $this->files->getSplashImage($value);
        }

        return null;
    }
}
