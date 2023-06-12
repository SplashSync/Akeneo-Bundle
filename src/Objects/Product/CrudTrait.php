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
     * @return null|Product
     */
    public function load(string $objectId): ?Product
    {
        //====================================================================//
        // Setup Splash Akeneo Connector
        $this->configuration->setup($this);
        //====================================================================//
        // Load Product from Repository
        $product = $this->repository->find($objectId);
        if (!($product instanceof Product)) {
            return Splash::log()->errNull("Unable to find Akeneo Product ".$objectId);
        }
        $this->gallery->clear();

        return $product;
    }

    //====================================================================//
    // PRODUCT CREATE
    //====================================================================//

    /**
     * @return null|Product
     */
    public function create(): ?Product
    {
        //====================================================================//
        // Setup Splash Akeneo Connector
        $this->configuration->setup($this);
        //====================================================================//
        // Create a New PIM Product
        $product = $this->crud->createProduct($this->in);
        if (null === $product) {
            return Splash::log()->errNull("Akeneo Product Create Failed");
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
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Forward to Crud Service
        if (!$this->crud->update($this->object)) {
            return null;
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
    public function delete(string $objectId): bool
    {
        //====================================================================//
        // Try Loading the Product
        $product = $this->load($objectId);
        if (!$product) {
            return true;
        }
        //====================================================================//
        // Forward to Crud Service
        return $this->crud->delete($product);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        if (empty($this->object)) {
            return null;
        }

        return $this->object->getUuid()->toString();
    }
}
