<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Akeneo\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Splash\Bundle\Helpers\Doctrine\AbstractEventSubscriber;
use Splash\Bundle\Models\AbstractConnector;

/**
 * Akeneo Product Doctrine Events Subscriber
 */
class ProductSubscriber extends AbstractEventSubscriber
{
    /**
     * List of Entities Managed by Splash
     *
     * @var array
     */
    protected static $entities = array(
        Product::class => "Product",
    );

    /**
     * Username used for Commits
     *
     * @var string
     */
    protected static $username = "Akeneo";

    /**
     * Username used for Commits
     *
     * @var string
     */
    protected static $commentPrefix = "Akeneo PIM";

    //====================================================================//
    //  Events Actions
    //====================================================================//

    /**
     * Disable All Events when Running the Installer
     */
    public function setAllDisabled(): void
    {
        self::setStates(false, false, false);
    }

    /**
     * Override Identifier Parser to Filter on Categories (if Feature Enabled)
     *
     * {@inheritdoc}
     */
    protected function getObjectIdentifiers(LifecycleEventArgs $eventArgs, AbstractConnector $connector): array
    {
        //====================================================================//
        // Get Impacted Object
        $product = $eventArgs->getEntity();
        //====================================================================//
        // Get List of Categories for this Connection
        if (!($product instanceof Product)) {
            return parent::getObjectIdentifiers($eventArgs, $connector);
        }
        //====================================================================//
        // Get List of Categories for this Connection
        $categoryCodes = $connector->getParameter("categories", array());
        if (!is_array($categoryCodes) || empty($categoryCodes)) {
            return parent::getObjectIdentifiers($eventArgs, $connector);
        }
        //====================================================================//
        // Walk on Product Categories
        foreach ($product->getCategories() as $category) {
            if ($this->isInFilteredCategories($categoryCodes, $category)) {
                return parent::getObjectIdentifiers($eventArgs, $connector);
            }
        }

        return array();
    }

    /**
     * Check if categorie is in Filtered Categories Tree
     *
     * @param array             $categoryCodes
     * @param CategoryInterface $category
     *
     * @return bool
     */
    private function isInFilteredCategories(array $categoryCodes, CategoryInterface $category): bool
    {
        if (in_array($category->getCode(), $categoryCodes, true)) {
            return true;
        }
        $parent = $category->getParent();
        if (null !== $parent) {
            return $this->isInFilteredCategories($categoryCodes, $parent);
        }

        return false;
    }
}
