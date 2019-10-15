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

namespace Splash\Akeneo\EventSubscriber;

use Pim\Component\Catalog\Model\Product;
use Splash\Bundle\Helpers\Doctrine\AbstractEventSubscriber;

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
}
