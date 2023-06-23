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

namespace Splash\Akeneo\Objects\Category;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface as Category;
use Exception;
use Splash\Core\SplashCore as Splash;

/**
 * Product CRUD Actions
 */
trait CrudTrait
{
    //====================================================================//
    // Category LOADING
    //====================================================================//

    /**
     * Load Category
     *
     * @param string $objectId
     *
     * @return null|Category
     */
    public function load(string $objectId): ?Category
    {
        //====================================================================//
        // Setup Splash Akeneo Connector
        $this->configuration->setup($this);
        //====================================================================//
        // Load Product from Repository
        $product = $this->repository->find($objectId);
        if (!($product instanceof Category)) {
            return Splash::log()->errNull("Unable to find Akeneo Category ".$objectId);
        }

        return $product;
    }

    //====================================================================//
    // Category CREATE
    //====================================================================//

    /**
     * @return null|Category
     */
    public function create(): ?Category
    {
        //====================================================================//
        // Setup Splash Akeneo Connector
        $this->configuration->setup($this);
        //====================================================================//
        // Check Customer Name is given
        if (empty($this->in["code"]) || !is_string($this->in["code"])) {
            return Splash::log()->errNull("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "code");
        }
        //====================================================================//
        // Create a New PIM Category
        /** @var Category $category */
        $category = $this->factory->create();
        $category->setCode($this->in['code']);
        //====================================================================//
        // Forward to Saver Service
        try {
            $this->saver->save($this->object);
        } catch (Exception $e) {
            Splash::log()->errNull($e->getMessage());

            return Splash::log()->errNull("Akeneo Category Create Failed");
        }
        //====================================================================//
        // Return a New Object
        return  $category;
    }

    //====================================================================//
    // Category UPDATE
    //====================================================================//

    /**
     * @param bool $needed
     *
     * @return null|string
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Forward to Saver Service
        try {
            if ($needed && !$this->saver->save($this->object)) {
                return null;
            }
        } catch (Exception $e) {
            return Splash::log()->errNull($e->getMessage());
        }
        //====================================================================//
        // Return Object Id
        return  $this->getObjectIdentifier();
    }

    //====================================================================//
    // Category Delete
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function delete(string $objectId): bool
    {
        //====================================================================//
        // Try Loading the Product
        $category = $this->load($objectId);
        if (!$category) {
            return true;
        }
        //====================================================================//
        // Forward to Crud Service
        return $this->remover->remove($category);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        if (empty($this->object)) {
            return null;
        }

        return (string) $this->object->getId();
    }
}
