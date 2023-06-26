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

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Splash\Models\Helpers\InlineHelper;

class CategoriesUpdater
{
    /**
     * @var string[]
     */
    private array $currentCodes = array();

    /**
     * @var string[]
     */
    private array $newCodes;

    public function __construct(
        ProductInterface $product,
        ?string $categoriesCodes
    ) {
        //====================================================================//
        // Collect List of Current Categories
        /** @var CategoryInterface $category */
        foreach ($product->getCategories() as $category) {
            $this->currentCodes[] = $category->getCode();
        }
        //====================================================================//
        // Collect List of New Categories
        $this->newCodes = InlineHelper::toArray($categoriesCodes);
    }

    /**
     * Get List of New Category Codes
     *
     * @return string[]
     */
    public function getAddedCodes(): array
    {
        return array_diff(
            $this->newCodes,
            $this->currentCodes,
        );
    }

    /**
     * Get List of Removed Category Codes
     *
     * @return string[]
     */
    public function getRemovedCodes(): array
    {
        return array_diff(
            $this->currentCodes,
            $this->newCodes
        );
    }
}
