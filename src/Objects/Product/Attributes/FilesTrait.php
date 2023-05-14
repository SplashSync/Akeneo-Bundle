<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
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
 * Manage Files Types Attributes
 * Import / Export of Product Attribute Values
 */
trait FilesTrait
{
//    /**
//     * FILE - Read Attribute Data with Local & Scope Detection
//     *
//     * @param Product   $product   Akeneo Product Object
//     * @param Attribute $attribute Akeneo Attribute Object
//     * @param string    $isoLang
//     * @param string    $channel
//     *
//     * @return mixed
//     */
//    protected function getFileValue(Product $product, Attribute $attribute, string $isoLang, string $channel)
//    {
//        $value = $this->getCoreValue($product, $attribute, $isoLang, $channel);
//
//        if ($value instanceof FileInfo) {
//            return $this->files->getSplashFile($value);
//        }
//
//        return null;
//    }

    /**
     * FILE - Write Attribute Data with Local & Scope Detection
     *
     * @param Product   $product   Akeneo Product Object
     * @param Attribute $attribute Akeneo Attribute Object
     * @param string    $isoLang
     * @param string    $channel
     * @param mixed     $data
     *
     * @return bool
     */
    protected function setFileValue(
        Product $product,
        Attribute $attribute,
        string $isoLang,
        string $channel,
        $data
    ): bool {
        //====================================================================//
        // Check If New File is Valid
        if (!is_array($data) || !$this->files->isValid($data)) {
            return $this->setCoreValue($product, $attribute, $isoLang, $channel, null);
        }
        //====================================================================//
        // Load Potential Current File from Storage
        $curentFile = $this->getCoreValue($product, $attribute, $isoLang, $channel);
        //====================================================================//
        // Update File from Splash
        if ($curentFile instanceof FileInfo) {
            //====================================================================//
            // Compare Current File & New file
            if ($this->files->isSimilar($curentFile, $data)) {
                return true;
            }
            //====================================================================//
            // Delete Current File from Filesystem
            if (!$this->files->delete($curentFile)) {
                return false;
            }
        }
        //====================================================================//
        // Add New File from Splash
        $newFile = $this->files->add($data);
        //====================================================================//
        // Update Akeneo Attribute Value
        if (null === $newFile) {
            return false;
        }

        return $this->setCoreValue($product, $attribute, $isoLang, $channel, (string) $newFile);
    }
}
