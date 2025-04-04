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

namespace Splash\Akeneo\Services;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Exception;
use Splash\Akeneo\Models\GalleryImage;
use Splash\Akeneo\Models\GalleryUpdater;
use Splash\Client\Splash;

/**
 * Manage Product Images Gallery Updates
 */
class GalleryManager
{
    /**
     * Images Information Cache
     *
     * @var null|array<string, GalleryImage>
     */
    private static ?array $cache = null;

    /**
     * Service Constructor
     *
     * @param AttributesManager $attr
     * @param VariantsManager   $variants
     * @param Configuration     $config
     */
    public function __construct(
        private readonly AttributesManager $attr,
        private readonly VariantsManager   $variants,
        private readonly LocalesManager    $locales,
        private readonly Configuration     $config,
    ) {
    }

    //====================================================================//
    // PRODUCTS GALLERY ACTION
    //====================================================================//

    /**
     * Return Product Images Information Array from Akeneo Product Object
     *
     * @throws Exception
     *
     * @return GalleryImage[]
     */
    public function getGalleryImages(ProductInterface $product): array
    {
        //====================================================================//
        // Ensure Cache is Loaded
        if (!isset(self::$cache)) {
            //====================================================================//
            // Ensure Init of Product Gallery
            self::$cache = self::$cache ?? array();
            GalleryImage::setStaticUuids($this->getVariantsUuids($product));
            //====================================================================//
            // Load Complete Product Images Gallery
            foreach ($this->config->getImagesCodes() as $imageCode) {
                $this->addAttributeToGallery($product, $imageCode);
            }
        }

        return self::$cache;
    }

    /**
     * Return Product Images Information Array from Akeneo Product Object
     *
     * @param ProductInterface $product
     * @param array[]          $receivedImages
     *
     * @throws Exception
     *
     * @return bool
     */
    public function setGalleryImages(ProductInterface $product, array $receivedImages): bool
    {
        //====================================================================//
        // Create Gallery Updater
        $updater = new GalleryUpdater(
            $product,
            $this->getCoverAttributeCode($product),
            $this->getFamilyVariantLevels($product),
            $this->variants->getVariantsSummary($product),
        );
        //====================================================================//
        // Compute Changes
        $updater->compute($this->getGalleryImages($product), $receivedImages);
        //====================================================================//
        // Apply Changes
        foreach ($updater->getUpdatedImagesSet() as $attrCode => $splashImage) {
            if (!$this->attr->set($product, $attrCode, $splashImage)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if Image is Part of Product Galley
     *
     * @param string $attrCode Attribute Code
     *
     * @return bool
     */
    public function isGalleryImage(string $attrCode): bool
    {
        return in_array($attrCode, array_keys($this->config->getImagesCodes()), true);
    }

    /**
     * Empty Cache for Product Gallery Images
     */
    public function clear(): void
    {
        self::$cache = null;
    }

    //====================================================================//
    // PRIVATE - GALLERY IMAGES MANAGEMENT
    //====================================================================//

    /**
     * Fetch Product Images Information for an Attribute Code, with Variants Detection
     *
     * @param ProductInterface                            $product
     * @param array{'code': string, 'label': null|string} $imageCode
     *
     * @throws Exception
     *
     * @return void
     */
    private function addAttributeToGallery(ProductInterface $product, array $imageCode): void
    {
        $attrCode = $imageCode['code'];
        //====================================================================//
        // Safety Check => Verify if FieldName is An Attribute Type
        if (!$this->attr->has($attrCode)) {
            return;
        }
        //====================================================================//
        // Read Product Splash Image
        $splImage = $this->getSplashImage($product, $attrCode);
        if ($splImage) {
            //====================================================================//
            // Add Image to Product Gallery
            $galleryImage = $this->addImageToGallery($product, $attrCode, $splImage);
            $this->addLabelsToGallery($product, $galleryImage, $attrCode, $imageCode['label'] ?? null);
        }
        //====================================================================//
        // Complete Cache with Other Variants Images
        foreach ($this->variants->getVariantsList($product, true) as $variant) {
            //====================================================================//
            // Skip Current Product
            if ($variant->getUuid() === $product->getUuid()) {
                continue;
            }
            //====================================================================//
            // Read Attribute Data
            $splImage = $this->getSplashImage($variant, $attrCode);
            if ($splImage) {
                //====================================================================//
                // Add Image to Product Gallery
                $galleryImage = $this->addImageToGallery($variant, $attrCode, $splImage);
                $this->addLabelsToGallery($product, $galleryImage, $attrCode, $imageCode['label'] ?? null);
            }
        }
    }

    /**
     * Add an Image to Gallery
     *
     * @param ProductInterface $product
     * @param string           $attrCode
     * @param array            $splashImage
     *
     * @return null|GalleryImage
     */
    private function addImageToGallery(ProductInterface $product, string $attrCode, array $splashImage): ?GalleryImage
    {
        //====================================================================//
        // Safety Checks
        $uuid = $product->getUuid()->toString();
        if (!$attrCode || !$uuid) {
            return null;
        }
        //====================================================================//
        // Add Image to Collection
        if (!isset(self::$cache[$attrCode])) {
            self::$cache[$attrCode] = new GalleryImage(
                $splashImage,
                ($attrCode == $this->getCoverAttributeCode($product)),
                $attrCode,
                $this->getFamilyVariantLevel($product, $attrCode),
            );
        }
        self::$cache[$attrCode]->setVisibility($uuid, true);

        return self::$cache[$attrCode];
    }

    /**
     * Add an Image to Gallery
     *
     * @param ProductInterface  $product
     * @param null|GalleryImage $galleryImage
     * @param string            $attrCode
     * @param null|string       $labelCode
     *
     * @return void
     */
    private function addLabelsToGallery(
        ProductInterface $product,
        ?GalleryImage $galleryImage,
        string $attrCode,
        ?string $labelCode
    ): void {
        if (!$galleryImage) {
            return;
        }

        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            try {
                $imgLabel = null;
                //====================================================================//
                // Read Label from Product Attribute
                if ($labelCode) {
                    if ($this->locales->isDefault($isoLang)) {
                        $imgLabel = $this->attr->get($product, $labelCode)[$labelCode] ?? null;
                    } else {
                        $attrCodeWithLang = $labelCode."_".$isoLang;
                        $imgLabel = $this->attr->get($product, $attrCodeWithLang)[$attrCodeWithLang] ?? null;
                    }
                }
                //====================================================================//
                // Read Label from Attribute Definition
                if (!$imgLabel) {
                    $attribute = $this->attr->find($attrCode);
                    $imgLabel = $attribute->getTranslation($isoLang)?->getLabel() ?? $attribute->getLabel();
                }

                $galleryImage->setLabel($isoLang, $imgLabel);
            } catch (Exception $e) {
                Splash::log()->report($e);

                continue;
            }
        }
    }

    //====================================================================//
    // PRIVATE - LOW LEVEL INFORMATION COLLECTORS
    //====================================================================//

    /**
     * Read Splash Image from Product Attribute
     *
     * @param ProductInterface $product
     * @param string           $attrCode
     *
     * @throws Exception
     *
     * @return null|array
     */
    private function getSplashImage(ProductInterface $product, string $attrCode): ?array
    {
        //====================================================================//
        // Read Current Product Attribute Data
        $splImages = $this->attr->get($product, $attrCode);
        if (empty($splImages[$attrCode]) || !is_array($splImages[$attrCode])) {
            return null;
        }

        return $splImages[$attrCode];
    }

    /**
     * Get Code of Attribute used as Product Cover Image
     *
     * @param ProductInterface $product
     *
     * @return null|string
     */
    private function getCoverAttributeCode(ProductInterface $product): ?string
    {
        return $product->getFamily()?->getAttributeAsImage()?->getCode();
    }

    /**
     * Get All Attributes Level from Product Family variant
     *
     * @param ProductInterface $product
     *
     * @return array<string, int>
     */
    private function getFamilyVariantLevels(ProductInterface $product): array
    {
        $levels = array();
        foreach (array_keys($this->config->getImagesCodes()) as $attrCode) {
            $levels[$attrCode] = $this->getFamilyVariantLevel($product, $attrCode);
        }
        asort($levels);

        return $levels;
    }

    /**
     * Get Attribute Level from Product Family variant
     *
     * @param ProductInterface $product
     * @param string           $attrCode
     *
     * @return int
     */
    private function getFamilyVariantLevel(ProductInterface $product, string $attrCode): int
    {
        //====================================================================//
        // Product Has Family Variant
        $familyVariant = $product->getFamilyVariant();
        if (null === $familyVariant) {
            return 0;
        }

        return $familyVariant->getLevelForAttributeCode($attrCode);
    }

    /**
     * Get List of All Variants Uuids
     *
     * @param ProductInterface $product
     *
     * @return array<string, bool>
     */
    private function getVariantsUuids(ProductInterface $product): array
    {
        global $uuids;

        if (!isset($uuids[$product->getUuid()->toString()])) {
            $uuids = array();
            foreach ($this->variants->getVariantsList($product) as $item) {
                if (is_string($item['rawId'] ?? null)) {
                    $uuids[$item['rawId']] = false;
                }
            }
        }

        return $uuids;
    }
}
