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

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Splash\Models\Objects\ImagesTrait as SplashImages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Manage Images Types Attributes
 * Import / Export of Product Attribute Values
 */
trait ImagesTrait
{
    use SplashImages;

//    /**
//     * @var string
//     */
//    private $catalogStorageDir;
//
//    /**
//     * @param ProductInterface   $object
//     * @param AttributeInterface $attribute
//     *
//     * @return array
//     */
//    protected function exportImage(ProductInterface $object, AttributeInterface $attribute)
//    {
//        //====================================================================//
//        // Check if Attribute is Used for this Object
//        if (!in_array($attribute->getCode(), $object->getUsedAttributeCodes(), true)) {
//            return null;
//        }
//
//        $image = $this->getAttributeValue($object, $attribute);
//
//        return self::Images()->Encode(
//            $image->getOriginalFilename(),
//            $image->getKey(),
//            $this->catalogStorageDir."/",
//            $this->Router->generate(
//                "pim_enrich_media_show",
//                array( "filename" => urlencode($image->getKey()), "filter" => "preview" ),
//                UrlGeneratorInterface::ABSOLUTE_URL
//            )
//        );
//    }
}
