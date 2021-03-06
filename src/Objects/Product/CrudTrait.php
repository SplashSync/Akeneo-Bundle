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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as Product;
use Splash\Core\SplashCore as Splash;

/**
 * Product CRUD Actions
 */
trait CrudTrait
{
    //====================================================================//
    // PRODUCT LOADING
    //====================================================================//

    /**
     * Load Product
     *
     * @param string $objectId
     *
     * @return false|Product
     */
    public function load($objectId)
    {
        //====================================================================//
        // Ensure Service Configuration
        $this->ensureSetup();
        //====================================================================//
        // Load Product from Repository
        $product = $this->repository->find($objectId);
        if (!($product instanceof Product)) {
            return Splash::Log()->errTrace("Unable to find Akeneo Product ".$objectId);
        }
        $this->flushImageCache();

        return $product;
    }

    //====================================================================//
    // PRODUCT CREATE
    //====================================================================//

    /**
     * @return false|Product
     */
    public function create()
    {
        //====================================================================//
        // Ensure Service Configuration
        $this->ensureSetup();
        //====================================================================//
        // Create a New PIM Product
        $product = $this->crud->createProduct($this->in);
        if (null === $product) {
            return Splash::Log()->errTrace("Akeneo Product Create Failled");
        }
        //====================================================================//
        // Return a New Object
        return  $product;
    }

    //====================================================================//
    // PRODUCT UPDATE
    //====================================================================//

    /**
     * {@inheritdoc}
     *
     * @param bool $needed
     *
     * @return false|string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update($needed)
    {
        //====================================================================//
        // Forward to Crud Service
        if (!$this->crud->update($this->object)) {
            return false;
        }
        //====================================================================//
        // Return Object Id
        return  $this->getObjectIdentifier();
    }

    //====================================================================//
    // PRODUCT CREATE
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Try Loading the Product
        $product = $this->load((string) $objectId);
        if (!$product) {
            return true;
        }

        //====================================================================//
        // Forward to Crud Service
        $this->crud->delete($product);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        if (empty($this->object)) {
            return false;
        }

        return (string) $this->object->getId();
    }
}
