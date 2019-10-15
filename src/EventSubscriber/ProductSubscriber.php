<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Splash\Akeneo\EventSubscriber;

use Splash\Bundle\Helpers\Doctrine\AbstractEventSubscriber;
use Pim\Component\Catalog\Model\Product;

/**
 * Akeneo Product Doctrine Events Subscriber
 */
class ProductSubscriber extends AbstractEventSubscriber {

    /**
     * List of Entities Managed by Splash
     *
     * @var array
     */
    protected static $entities = array(
        Product::class => "Product"
    );

    /**
     * Username used for Commits
     *
     * @var array
     */
    protected static $username = "Akeneo";

    /**
     * Username used for Commits
     *
     * @var array
     */
    protected static $commentPrefix = "Akeneo PIM";
    
}
