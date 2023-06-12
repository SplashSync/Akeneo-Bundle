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
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildImagesFields(): void
    {
        //====================================================================//
        // Safety Check => Images List is Given
        if (empty($this->configuration->getImagesCodes())) {
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
            ->isNotTested()
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
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @throws Exception
     *
     * @return void
     */
    protected function getImagesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "images", $fieldName);
        if (!$fieldId) {
            return;
        }
        $galleryImages = $this->gallery->getGalleryImages($this->object);
        //====================================================================//
        // For All Available Product Images
        $index = 0;
        foreach ($galleryImages as $galleryImage) {
            //====================================================================//
            // Prepare
            switch ($fieldId) {
                case "image":
                    $value = $galleryImage->image();

                    break;
                case "position":
                    $value = $index;

                    break;
                case "visible":
                    $value = $galleryImage->isVisible($this->object->getUuid()->toString());

                    break;
                case "cover":
                    $value = $galleryImage->isCover($this->object->getUuid()->toString());

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
     * Write Given Fields
     *
     * @param string       $fieldName Field Identifier / Name
     * @param null|array[] $fieldData Field Data
     *
     * @throws Exception
     */
    protected function setImagesFields(string $fieldName, ?array $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT IMAGES
            //====================================================================//
            case 'images':
                //====================================================================//
                // Update Gallery
                if (!$this->gallery->setGalleryImages($this->object, $fieldData ?? array())) {
                    return;
                }

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
