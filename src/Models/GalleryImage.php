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

use Splash\Client\Splash;

/**
 * Product Gallery Image Item
 */
class GalleryImage
{
    private string $md5;

    /**
     * @var array<string, bool>
     */
    private static array $staticUuids = array();

    /**
     * @var array<string, bool>
     */
    private array $uuids = array();

    /**
     * @param array       $splashImage Splash Image
     * @param bool        $isCover
     * @param null|string $code        Attribute Code
     * @param null|int    $level       Family Variant Level
     */
    public function __construct(
        private array  $splashImage,
        private bool   $isCover = false,
        private ?string $code = null,
        private ?int    $level = null,
    ) {
        $this->md5 = $this->splashImage['md5'];
        $this->uuids = self::$staticUuids;
    }

    //====================================================================//
    // MAIN ACTIONS
    //====================================================================//

    /**
     * Set Splash Image
     *
     * @param array $splashImage
     *
     * @return self
     */
    public function setImage(array $splashImage): self
    {
        $this->splashImage = $splashImage;

        return $this;
    }

    /**
     * Set up a Product Using this Image
     *
     * @param string $uuid
     * @param bool   $isVisible
     *
     * @return self
     */
    public function setVisibility(string $uuid, bool $isVisible): self
    {
        $this->uuids[$uuid] = $isVisible;

        return $this;
    }

    /**
     * Set up All Products Uuids
     *
     * @param array<string, bool> $uuids
     *
     * @return void
     */
    public static function setStaticUuids(array $uuids): void
    {
        self::$staticUuids = $uuids;
    }

    /**
     * Detect Images Optimal Level
     *
     * @param array<array|string> $summary
     * @param null|string[]       $visibleUuids
     * @param int                 $level
     *
     * @return null|int
     */
    public function getOptimalLevel(array $summary, array $visibleUuids = null, int $level = 0): ?int
    {
        $visibleUuids = $visibleUuids ?? $this->visibleUuids();
        //====================================================================//
        // Test at Current Level
        $flatUuids = self::toFlatUuids($summary);
        if ($flatUuids == $visibleUuids) {
            return $level;
        }
        //====================================================================//
        // Test at Next Level
        foreach ($summary as $uuidOrSummary) {
            //====================================================================//
            // This is Not Lowest Level
            if (is_array($uuidOrSummary)) {
                if ($result = self::getOptimalLevel($uuidOrSummary, $visibleUuids, $level++)) {
                    return $result;
                }

                continue;
            }
            //====================================================================//
            // This is Lowest Level
            if ((array($uuidOrSummary) == $visibleUuids)) {
                return $level + 1;
            }
        }

        return null;
    }

    //====================================================================//
    // MANAGE IMAGE ALLOCATION => PRODUCT ATTRIBUTE
    //====================================================================//

    /**
     * Check if this Image Has Attribute Allocation
     *
     * @return bool
     */
    public function hasAllocation(): bool
    {
        return !empty($this->code) && !is_null($this->level);
    }

    /**
     * Mark this Image for re-allocation
     *
     * @return void
     */
    public function deleteAllocation(): void
    {
        $this->code = null;
        $this->level = null;
    }

    /**
     * Set Image new Allocation
     *
     * @param string $code
     * @param int    $level
     *
     * @return void
     */
    public function setAllocation(string $code, int $level): void
    {
        $this->code = $code;
        $this->level = $level;
    }

    //====================================================================//
    // SIMPLE GETTERS
    //====================================================================//

    /**
     * Check if Image is Visible on Product
     *
     * @param string $uuid
     *
     * @return bool
     */
    public function isVisible(string $uuid): bool
    {
        //====================================================================//
        // Check if Image is Linked to Product
        return $this->uuids[$uuid] ?? false;
    }

    /**
     * Check if Image is Cover Image
     *
     * @param string $uuid
     *
     * @return bool
     */
    public function isCover(string $uuid): bool
    {
        return $this->isCover && $this->isVisible($uuid);
    }

    /**
     * Check if Image is to Delete
     *
     * @return bool
     */
    public function isDeleted(): bool
    {
        return (-1 == $this->level);
    }

    /**
     * Get Image Md5
     *
     * @return string
     */
    public function md5(): string
    {
        return $this->md5;
    }

    /**
     * Get Attribute Code
     *
     * @return null|string
     */
    public function code(): ?string
    {
        return $this->code;
    }

    /**
     * Get Family Variant Level
     *
     * @return null|int
     */
    public function level(): ?int
    {
        return $this->level;
    }

    /**
     * Get Splash Image
     *
     * @return array
     */
    public function image(): array
    {
        return $this->splashImage;
    }

    /**
     * Get Products Uuids
     *
     * @return array<string, bool>
     */
    public function uuids(): array
    {
        return $this->uuids;
    }

    /**
     * Get Visible Products Uuids
     *
     * @return string[]
     */
    public function visibleUuids(): array
    {
        return array_keys(array_filter($this->uuids));
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Flatten All Child Uuids
     *
     * @param array<array|string> $uuids
     *
     * @return string[]
     */
    private static function toFlatUuids(array $uuids): array
    {
        $flat = array();
        foreach ($uuids as $item) {
            if (is_array($item)) {
                $flat = array_merge($flat, self::toFlatUuids($item));
            } else {
                $flat[] = $item;
            }
        }

        return $flat;
    }
}
