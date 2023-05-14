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

use Exception;
use Splash\Models\Objects\ImagesTrait as SplashImagesTrait;

/**
 * Access to Product Images Fields
 */
trait ImagesTrait
{
    use SplashImagesTrait;

    /**
     * Images Information Cache
     *
     * @var null|array
     */
    private ?array $imagesCache = null;

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
            ->identifier("image")
            ->inList("images")
            ->name("Image")
            ->group($groupName)
            ->microData("http://schema.org/Product", "image")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Images => Position
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("position")
            ->inList("images")
            ->name("Position")
            ->microData("http://schema.org/Product", "positionImage")
            ->group($groupName)
            ->isNotTested()
        ;
        //====================================================================//
        // Product Images => Is Cover
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("cover")
            ->inList("images")
            ->name("Cover")
            ->microData("http://schema.org/Product", "isCover")
            ->group($groupName)
            ->isNotTested()
        ;
        //====================================================================//
        // Product Images => Is Visible Image
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("visible")
            ->inList("images")
            ->name("Visible")
            ->microData("http://schema.org/Product", "isVisibleImage")
            ->group($groupName)
            ->isNotTested()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     * @throws Exception
     */
    protected function getImagesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "images", $fieldName);
        if (!$fieldId) {
            return;
        }
        $imgCache = $this->getImagesCache();
        //====================================================================//
        // For All Available Product Images
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
            self::lists()->insert($this->out, "images", $fieldName, $index, $value);
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
        $config = $this->getParameter("images", array());
        if (!is_array($config) || empty($config)) {
            return false;
        }

        return in_array($attrCode, $config, true);
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
        $family = $this->object->getFamily();
        if (null === $family) {
            return (0 == $index);
        }

        $attributeAsImage = $family->getAttributeAsImage();

        if (null === $attributeAsImage) {
            return (0 == $index);
        }

        return ($attrCode == $attributeAsImage->getCode());
    }

    /**
     * Return Product Images Information Array from Akeneo Product Object
     *
     * @return array
     * @throws Exception
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
        $config = $this->getParameter("images", array());
        $config = is_array($config) ? $config : array();
        foreach ($config as $attrCode) {
            $this->getImageCache($attrCode);
        }

        return is_array($this->imagesCache) ? $this->imagesCache : array();
    }

    /**
     * Fetch Product Images Information for an Attribute Code, with Variants Detection
     *
     * @param string $attrCode
     *
     * @return void
     * @throws Exception
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
            if ($variant->getUuid() === $this->object->getUuid()) {
                continue;
            }
            //====================================================================//
            // Read Attribute Data
            $rawVariantValue = $this->attr->get($variant, $attrCode);
            if (empty($rawVariantValue[$attrCode])) {
                continue;
            }
            //====================================================================//
            // Skip Similar Images
            if ($rawVariantValue[$attrCode]['md5'] == $rawValue[$attrCode]['md5']) {
                continue;
            }
            //====================================================================//
            // Add Image to Cache
            $this->imagesCache[$attrCode.'_'.$variant->getUuid()->toString()] = array(
                "image" => $rawVariantValue[$attrCode],
                "visible" => false,
            );
        }
    }
}
