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

namespace Splash\Akeneo\Models;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Splash\Client\Splash;

/**
 * Product Images Gallery Updater
 */
class GalleryUpdater
{
    const KEY_IMAGE = "image";
    const KEY_VISIBLE = "visible";
    const KEY_COVER = "cover";
    const KEY_POSITION = "position";

    /**
     * Current Product Uuid
     *
     * @var string
     */
    private string $uuid;

    /**
     * Target Products Images by MD5
     *
     * @var array<string, GalleryImage>
     */
    private array $images = array();

    /**
     * @param ProductInterface   $product   Current Product
     * @param null|string        $coverCode Cover Image Attribute Code
     * @param array<string, int> $levels    List of Images Attributes Codes to Level
     * @param array              $summary   Products Uuids Summary
     */
    public function __construct(
        private readonly ProductInterface $product,
        private readonly ?string $coverCode,
        private readonly array $levels,
        private readonly array $summary
    ) {
        $this->uuid = $this->product->getUuid()->toString();
    }

    /**
     * Compute New Images Configuration for Product
     *
     * @param GalleryImage[] $galleryImages
     * @param array[]        $receivedImages
     *
     * @return GalleryImage[]
     */
    public function compute(array $galleryImages, array $receivedImages): array
    {
        $this->images = array();
        //====================================================================//
        // Ensure Ordering of Received Images
        $this->orderReceivedImages($receivedImages);
        //====================================================================//
        // Manage Cover Image
        $this->computeCoverImage($receivedImages);
        //====================================================================//
        // Manage Other Images
        $this->computeUpdatedImages($galleryImages, $receivedImages);
        //====================================================================//
        // Manage Deleted Images
        $this->computeDeletedImages($galleryImages);
        //====================================================================//
        // Compute Images Allocations
        $this->computeAllocations();

        return $this->images;
    }

    /**
     * Get Updated Images Set
     *
     * @return array<string, null|array>
     */
    public function getUpdatedImagesSet(): array
    {
        $imagesSet = array();
        //====================================================================//
        // Walk on Images Attributes
        foreach (array_keys($this->levels) as $attrCode) {
            //====================================================================//
            // Walk on Gallery Images
            foreach ($this->images as $galleryImage) {
                //====================================================================//
                // Image Already Allocated or to Delete
                if (!$galleryImage->hasAllocation() || $galleryImage->isDeleted()) {
                    continue;
                }
                //====================================================================//
                // Image Not Located at This Attribute
                if ($galleryImage->code() != $attrCode) {
                    continue;
                }
                $imagesSet[$attrCode] = $galleryImage->image();
            }
            //====================================================================//
            // No Image Found => set NULL
            $imagesSet[$attrCode] = $imagesSet[$attrCode] ?? null;
        }

        return $imagesSet;
    }

    /**
     * Compute Attributes Allocations for Images
     *
     * @return void
     */
    public function computeAllocations(): void
    {
        //====================================================================//
        // Get Levels without Cover Image Attribute
        $levelsNoCover = $this->levels;
        if ($this->coverCode && isset($levelsNoCover[$this->coverCode])) {
            unset($levelsNoCover[$this->coverCode]);
        }
        //====================================================================//
        // Walk on New Gallery Images
        foreach ($this->images as $image) {
            //====================================================================//
            // Image Already Allocated or to Delete
            if ($image->hasAllocation() || $image->isDeleted()) {
                continue;
            }
            //====================================================================//
            // Detect Best Image Level
            $newLevel = $image->getOptimalLevel($this->summary);
            //====================================================================//
            // Detect Next Attr Code Level
            $newAttrCode = self::getNextLevelAttribute($levelsNoCover, $newLevel);
            //====================================================================//
            // Setup Gallery Image Allocation
            if (!is_null($newLevel) && $newAttrCode) {
                $image->setAllocation($newAttrCode, $newLevel);

                continue;
            }

            Splash::log()->err("Unable to allocate image ".($image->image()["name"] ?? null));
        }
    }

    /**
     * Get Next Available Attribute for Level
     *
     * @param array<string, int> $levelsNoCover
     * @param null|int           $level
     *
     * @return null|string
     */
    public static function getNextLevelAttribute(array &$levelsNoCover, ?int $level): ?string
    {
        if (is_null($level)) {
            return null;
        }
        foreach ($levelsNoCover as $attrCode => $attrLevel) {
            if ($attrLevel == $level) {
                unset($levelsNoCover[$attrCode]);

                return $attrCode;
            }
        }

        return null;
    }

    /**
     * Identify New Cover Image for Product
     *
     * @param array[] $receivedImages
     *
     * @return void
     */
    private function computeCoverImage(array &$receivedImages): void
    {
        //====================================================================//
        // No Cover Image Defined
        if (!$this->coverCode) {
            return;
        }
        //====================================================================//
        // No Cover Image Received
        if (!$receivedCover = $this->extractReceivedCover($receivedImages)) {
            return;
        }
        //====================================================================//
        // Register Cover Image
        $md5 = $receivedCover["md5"] ?? null;
        if ($md5 && is_string($md5)) {
            $this->images[$md5] = new GalleryImage(
                $receivedCover,
                true,
                $this->coverCode,
                $this->levels[$this->coverCode],
            );
            $this->images[$md5]->setVisibility($this->uuid, true);
        }
    }

    /**
     * Detect New & Updated Images for Product
     *
     * @param GalleryImage[] $galleryImages
     * @param array[]        $receivedImages
     *
     * @return void
     */
    private function computeUpdatedImages(array $galleryImages, array $receivedImages): void
    {
        //====================================================================//
        // Walk on Received Images
        foreach ($receivedImages as $receivedImage) {
            //====================================================================//
            // Extract Next Image Metadata
            $splashImage = $receivedImage[self::KEY_IMAGE] ?? null;
            $md5 = (string) ($receivedImage[self::KEY_IMAGE]["md5"] ?? null);
            if (!is_array($splashImage) || empty($md5)) {
                continue;
            }
            //====================================================================//
            // Configure Gallery Image
            $this->images[$md5] = $galleryImages[$md5] ?? new GalleryImage(
                $splashImage,
                false
            );
            $this->images[$md5]
                ->setImage($splashImage)
                ->setVisibility($this->uuid, $receivedImage[self::KEY_VISIBLE] ?? true)
                ->deleteAllocation()
            ;
        }
    }

    /**
     * Detect Deleted Images for Product
     *
     * @param GalleryImage[] $galleryImages
     *
     * @return void
     */
    private function computeDeletedImages(array $galleryImages): void
    {
        //====================================================================//
        // Walk on Gallery Images
        foreach ($galleryImages as $galleryImage) {
            $md5 = $galleryImage->md5();
            if (!isset($this->images[$md5])) {
                $this->images[$md5] = $galleryImage;
                $this->images[$md5]->setAllocation((string) $galleryImage->code(), -1);
            }
        }
    }

    //====================================================================//
    // RECEIVED GALLERY ANALYZE
    //====================================================================//

    /**
     * Order Received Images List
     *
     * @param array $receivedImages
     *
     * @return void
     */
    private function orderReceivedImages(array &$receivedImages): void
    {
        usort($receivedImages, function ($imageA, $imageB) {
            $posA = $imageA[self::KEY_POSITION] ?? 0;
            $posB = $imageB[self::KEY_POSITION] ?? 0;

            return ($posA < $posB) ? -1 : 1;
        });
    }

    /**
     * Detect Cover in Received Images
     *
     * @param array[] $receivedImages
     *
     * @return null|array
     */
    private function extractReceivedCover(array &$receivedImages): ?array
    {
        //====================================================================//
        // Walk on Received Images
        foreach ($receivedImages as $key => $receivedImage) {
            //====================================================================//
            // Is Cover
            if ($receivedImage[self::KEY_COVER] ?? false) {
                unset($receivedImages[$key]);

                return $receivedImage[self::KEY_IMAGE] ?? null;
            }
        }

        return null;
    }
}
