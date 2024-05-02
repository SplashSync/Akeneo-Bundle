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

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;

/**
 * Filter Products by Categories Codes
 */
class CategoriesPresence
{
    /**
     * Check if Product is in Listed Categories Codes
     *
     * @param string[] $categoryCodes
     */
    public static function isInCategoriesTree(Product|ProductModel $product, array $categoryCodes): bool
    {
        //====================================================================//
        // List of Categories is Empty
        if (empty($categoryCodes)) {
            return false;
        }
        //====================================================================//
        // Walk on Product Categories
        /** @var CategoryInterface $category */
        foreach ($product->getCategories() as $category) {
            if (self::isInFilteredCategories($categoryCodes, $category)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if category is in Filtered Categories Tree
     *
     * @param string[] $categoryCodes
     */
    private static function isInFilteredCategories(array $categoryCodes, CategoryInterface $category): bool
    {
        if (in_array($category->getCode(), $categoryCodes, true)) {
            return true;
        }
        $parent = $category->getParent();
        if (null !== $parent) {
            return self::isInFilteredCategories($categoryCodes, $parent);
        }

        return false;
    }
}
