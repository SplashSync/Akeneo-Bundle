<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Akeneo\Objects\Product;

use Pim\Component\Catalog\Model\Product;
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
        // Load Product from Repository
        $product = $this->repository->find($objectId);
        if (!($product instanceof Product)) {
            return Splash::Log()->errTrace("Unable to find Akeneo Product ".$objectId);
        }

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
        $product = $this->load($objectId);
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
