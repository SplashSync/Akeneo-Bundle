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

namespace Splash\Akeneo\Objects\Product;

use Splash\Models\Objects\ImagesTrait as SplashImagesTrait;

/**
 * Access to Product Images Fields
 */
trait ImagesTrait
{
    use SplashImagesTrait;

    /**
     * Images Informations Cache
     *
     * @var null|array
     */
    private $imagesCache;

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildImagesFields()
    {
        //====================================================================//
        // Safety Check => Images List is Given
        if (empty($this->getParameter("images", array()))) {
            return;
        }

        //====================================================================//
        // PRODUCT IMAGES
        //====================================================================//

        $groupName = "Images Gallery";

        //====================================================================//
        // Product Images List
        $this->fieldsFactory()->create(SPL_T_IMG)
            ->Identifier("image")
            ->InList("images")
            ->Name("Image")
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "image")
            ->isReadOnly();

        //====================================================================//
        // Product Images => Position
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("position")
            ->InList("images")
            ->Name("Position")
            ->MicroData("http://schema.org/Product", "positionImage")
            ->Group($groupName)
            ->isNotTested();

        //====================================================================//
        // Product Images => Is Cover
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("cover")
            ->InList("images")
            ->Name("Cover")
            ->MicroData("http://schema.org/Product", "isCover")
            ->Group($groupName)
            ->isNotTested();

        //====================================================================//
        // Product Images => Is Visible Image
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("visible")
            ->InList("images")
            ->Name("Visible")
            ->MicroData("http://schema.org/Product", "isVisibleImage")
            ->Group($groupName)
            ->isNotTested();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getImagesFields($key, $fieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "images", $fieldName);
        if (!$fieldId) {
            return;
        }
        $imgCache = $this->getImagesCache();
        //====================================================================//
        // For All Availables Product Images
        $index = 0;
        foreach ($imgCache as $attrCode => $image) {
            //====================================================================//
            // Prepare
            switch ($fieldId) {
                case "image":
                    $value = $image['image'];

                    break;
                case "position":
                    $value = $index;

                    break;
                case "visible":
                    $value = $image['visible'];

                    break;
                case "cover":
                    $value = $this->isCoverImage($attrCode, $index);

                    break;
                default:
                    return;
            }
            //====================================================================//
            // Insert Data in List
            self::lists()->Insert($this->out, "images", $fieldName, $index, $value);
            $index++;
        }
        unset($this->in[$key]);
    }

    /**
     * Check if Image is Part of Product Galley
     *
     * @param string $attrCode Attribute Code
     *
     * @return bool
     */
    protected function isGalleryImage(string $attrCode): bool
    {
        if (empty($this->getParameter("images", array()))) {
            return false;
        }

        return in_array($attrCode, $this->getParameter("images", array()), true);
    }

    /**
     * Flush Product Images Reading Cache
     *
     * @return void
     */
    protected function flushImageCache()
    {
        $this->imagesCache = null;
    }

    /**
     * Check if Image is Cover
     *
     * @param string $attrCode
     * @param int    $index
     *
     * @return bool
     */
    private function isCoverImage(string $attrCode, int $index): bool
    {
        $familly = $this->object->getFamily();
        if (null === $familly) {
            return (0 == $index);
        }

        $attributeAsImage = $familly->getAttributeAsImage();

        if (null === $attributeAsImage) {
            return (0 == $index);
        }

        return ($attrCode == $attributeAsImage->getCode());
    }

    /**
     * Return Product Images Informations Array from Akeneo Product Object
     *
     * @return array
     */
    private function getImagesCache(): array
    {
        //====================================================================//
        // Get Images Infos From Cache
        if (is_array($this->imagesCache)) {
            return $this->imagesCache;
        }
        $this->imagesCache = array();
        //====================================================================//
        // Load Complete Product Images List
        foreach ($this->getParameter("images", array()) as $attrCode) {
            $this->getImageCache($attrCode);
        }

        return is_array($this->imagesCache) ? $this->imagesCache : array();
    }

    /**
     * Fetch Product Images Informations for an Attribute Code, with Variants Detection
     *
     * @param string $attrCode
     *
     * @return void
     */
    private function getImageCache(string $attrCode): void
    {
        //====================================================================//
        // Safety Check => Verify if FieldName is An Attribute Type
        if (!$this->attr->has($attrCode)) {
            return;
        }

        //====================================================================//
        // Read Current Product Attribute Data
        $rawValue = $this->attr->get($this->object, $attrCode);
        if (isset($rawValue[$attrCode]) && !empty($rawValue[$attrCode])) {
            //====================================================================//
            // Add Image to Cache
            $this->imagesCache[$attrCode] = array(
                "image" => $rawValue[$attrCode],
                "visible" => true,
            );
        }

        //====================================================================//
        // Complete Cache with Other Variants Images
        foreach ($this->variants->getVariantsList($this->object, true) as $variant) {
            //====================================================================//
            // Skip Current Product
            if ($variant->getId() == $this->object->getId()) {
                continue;
            }
            //====================================================================//
            // Read Attribute Data
            $rawVariantValue = $this->attr->get($variant, $attrCode);
            if (!isset($rawVariantValue[$attrCode]) || empty($rawVariantValue[$attrCode])) {
                continue;
            }
            //====================================================================//
            // Skip Similar Images
            if ($rawVariantValue[$attrCode]['md5'] == $rawValue[$attrCode]['md5']) {
                continue;
            }
            //====================================================================//
            // Add Image to Cache
            $this->imagesCache[$attrCode.'_'.$variant->getId()] = array(
                "image" => $rawVariantValue[$attrCode],
                "visible" => false,
            );
        }
    }
}
