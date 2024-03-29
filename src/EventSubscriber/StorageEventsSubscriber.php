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

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Splash\Bundle\Models\AbstractEventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscriber to commit Products Updated by Mass Job Execution
 */
class StorageEventsSubscriber extends AbstractEventSubscriber implements EventSubscriberInterface
{
    use ObjectIdentifierTrait;

    /**
     * @inheritdoc
     */
    protected static array $classMap = array(
        Product::class => "Product",
        ProductModel::class => "Product",
        Category::class => "Category",
    );

    /**
     * @inheritdoc
     */
    protected static array $subscribedEvents = array(
        StorageEvents::POST_SAVE => array('onSave', 1000),
        StorageEvents::PRE_REMOVE => array('onRemove', 1000),
    );

    /**
     * Username used for Commits
     *
     * @var string
     */
    protected static string $username = "Akeneo";

    /**
     * Username used for Commits
     *
     * @var string
     */
    protected static string $commentPrefix = "Akeneo PIM";

    //====================================================================//
    //  Subscriber
    //====================================================================//

    /**
     * Return the subscribed events, their methods and priorities
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return static::$subscribedEvents;
    }

    //====================================================================//
    //  Events Actions
    //====================================================================//

    /**
     * When a Single Product is Saved.
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function onSave(GenericEvent $event) : void
    {
        $this->doEventAction(StorageEvents::POST_SAVE, $event, SPL_A_UPDATE);
    }

    /**
     * When a Single Product is Removed.
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function onRemove(GenericEvent $event) : void
    {
        $this->doEventAction(StorageEvents::POST_SAVE, $event, SPL_A_UPDATE);
    }
}
