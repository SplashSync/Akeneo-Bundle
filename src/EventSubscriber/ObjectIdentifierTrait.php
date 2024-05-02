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

namespace Splash\Akeneo\EventSubscriber;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Splash\Akeneo\Models\CategoriesPresence;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Client\Splash;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Tooling for Collecting Products Ids to Commit
 */
trait ObjectIdentifierTrait
{
    /**
     * @param GenericEvent|LifecycleEventArgs $event
     * @param AbstractConnector               $connector
     *
     * @throws Exception
     *
     * @return array
     */
    protected function getObjectIdentifiers($event, AbstractConnector $connector): array
    {
        //====================================================================//
        // Get Impacted Category
        $category = self::getCategory($event);
        if ($category) {
            return array((string) $category->getId());
        }
        //====================================================================//
        // Get Impacted Object
        /** @var null|Product|ProductModel $product */
        $product = self::getProduct($event);
        if (is_null($product)) {
            return array();
        }
        //====================================================================//
        // Ensure Product is Not Filtered by Categories
        if (!$this->isInAllowedCategory($connector, $product)) {
            return array();
        }

        return ($product instanceof ProductModel)
            ? self::getProductModelIdentifiers($product)
            : array($product->getUuid()->toString())
        ;
    }

    /**
     * Retrieve Ids of All Model
     *
     * @param ProductModel $productModel
     *
     * @return array
     */
    protected static function getProductModelIdentifiers(ProductModel $productModel): array
    {
        $productIds = array();
        //====================================================================//
        // Safety Check => No Commits in Server Mode
        if (Splash::isServerMode()) {
            return $productIds;
        }
        //====================================================================//
        // Walk on All Direct Child Products
        /** @var Product $product */
        foreach ($productModel->getProducts() as $product) {
            $productIds[] = $product->getUuid()->toString();
        }
        //====================================================================//
        // Walk on All Child ProductModels
        /** @var ProductModel $model */
        foreach ($productModel->getProductModels() as $model) {
            $productIds = array_replace_recursive(
                $productIds,
                self::getProductModelIdentifiers($model)
            );
        }

        return array_unique($productIds);
    }

    /**
     * @param GenericEvent|LifecycleEventArgs $event
     *
     * @return null|Product|ProductModel
     */
    private static function getProduct($event): ?object
    {
        //====================================================================//
        // Get Impacted Object
        $product = null;
        if ($event instanceof LifecycleEventArgs) {
            $product = $event->getObject();
        }
        if ($event instanceof GenericEvent) {
            $product = $event->getSubject();
        }
        //====================================================================//
        // Get List of Categories for this Connection
        if (!($product instanceof Product) && !($product instanceof ProductModel)) {
            return null;
        }

        return $product;
    }

    /**
     * @param GenericEvent|LifecycleEventArgs $event
     *
     * @return null|CategoryInterface
     */
    private static function getCategory($event): ?object
    {
        //====================================================================//
        // Get Impacted Object
        $object = null;
        if ($event instanceof LifecycleEventArgs) {
            $object = $event->getObject();
        }
        if ($event instanceof GenericEvent) {
            $object = $event->getSubject();
        }
        //====================================================================//
        // Get List of Categories for this Connection
        if (!($object instanceof CategoryInterface)) {
            return null;
        }

        return $object;
    }

    /**
     * If Categories Filter is Active, Ensure Product is part of it!
     */
    private function isInAllowedCategory(AbstractConnector $connector, Product|ProductModel $product): bool
    {
        //====================================================================//
        // Get List of Categories for this Connection
        $categoryCodes = $connector->getParameter("categories", array());
        if (!is_array($categoryCodes) || empty($categoryCodes)) {
            return true;
        }

        return CategoriesPresence::isInCategoriesTree($product, $categoryCodes);
    }
}
