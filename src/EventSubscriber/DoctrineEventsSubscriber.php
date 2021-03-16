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

namespace Splash\Akeneo\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Doctrine\ORM\Events;
use Splash\Bundle\Helpers\Doctrine\AbstractEventSubscriber;
use Splash\Bundle\Services\ConnectorsManager;

/**
 * Akeneo Product Doctrine Events Subscriber
 */
class DoctrineEventsSubscriber extends AbstractEventSubscriber
{
    use ObjectIdentifierTrait;

    /**
     * {@inheritdoc}
     */
    protected static $classMap = array(
        Product::class => "Product",
        ProductModel::class => "Product",
    );

    /**
     * {@inheritdoc}
     */
    protected static $username = "Akeneo";

    /**
     * {@inheritdoc}
     */
    protected static $commentPrefix = "Akeneo PIM";

    //====================================================================//
    //  CONSTRUCTOR
    //====================================================================//

    /**
     * Service Constructor
     *
     * @param ConnectorsManager $manager
     */
    public function __construct(ConnectorsManager $manager)
    {
        parent::__construct($manager);
        // Use Kernel Events for Update, better Compatibility
        static::setState(Events::postUpdate, false);
    }
}
