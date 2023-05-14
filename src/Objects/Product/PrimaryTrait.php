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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Splash\Client\Splash;

/**
 * Manage Search Products by Primary Key
 */
trait PrimaryTrait
{
    /**
     * @inheritDoc
     */
    public function getByPrimary(array $keys): ?string
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Extract Product Identifier
        $identifier = array_shift($keys);
        if (empty($identifier) || !is_string($identifier)) {
            return null;
        }
        //====================================================================//
        // Search Product by SKU
        /** @var null|ProductInterface $product */
        $product = $this->repository->findOneByIdentifier($identifier);
        //====================================================================//
        // Ensure Single Result Found
        if ($product) {
            return $product->getUuid()->toString();
        }

        return null;
    }
}
