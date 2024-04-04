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

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Product Categories Links Fields Access
 */
trait CategoriesLinksTrait
{
    /**
     * @var string Name of Categories ID List
     */
    private static string $catListName = "category";

    //====================================================================//
    // PRODUCT CATEGORIES INFOS
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    public function buildCategoriesLinksFields(): void
    {
        //====================================================================//
        // Product Categories Codes
        $this->fieldsFactory()->create((string) self::objects()->encode("Category", SPL_T_ID))
            ->identifier("id")
            ->name("ID")
            ->inList(self::$catListName)
            ->microData("http://schema.org/Product", "categories")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Categories IDs List
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("code")
            ->name("Code")
            ->inList(self::$catListName)
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    public function getCategoriesLinksFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, self::$catListName, $fieldName);
        if (!$fieldId) {
            return;
        }
        /** @var ArrayCollection<Category> $categories */
        $categories = $this->object->getCategories();
        //====================================================================//
        // For All Available Product Categories
        $index = 0;
        foreach ($categories as $category) {
            //====================================================================//
            // Safety Check =>> Category Root is Allowed
            if (!$this->configuration->isAllowedCategory($category)) {
                continue;
            }
            //====================================================================//
            // Prepare
            switch ($fieldId) {
                case "id":
                    $value = self::objects()->encode("Category", (string) $category->getId());

                    break;
                case "code":
                    $value = $category->getCode();

                    break;
                default:
                    return;
            }
            //====================================================================//
            // Insert Data in List
            self::lists()->insert($this->out, self::$catListName, $fieldName, $index, $value);
            $index++;
        }
        unset($this->in[$key]);
    }
}
