<?php

namespace Splash\Akeneo\Objects;

use Splash\Bundle\Annotation as SPL;

use Splash\Akeneo\Objects\Core\ObjectMetadataTrait;

use Splash\Akeneo\Objects\Product\CoreTrait;

/**
 * @abstract    Description of Product Object
 *
 * @author B. Paquier <contact@splashsync.com>
 * @SPL\Object( type                    =   "Product",
 *              name                    =   "Product",
 *              description             =   "Akeneo Product Object",
 *              icon                    =   "fa fa-product-hunt",
 *              enable_push_created     =    false,
 *              target                  =   "Pim\Component\Catalog\Model\Product",
 *              transformer_service     =   "Splash.Akeneo.Products.Transformer",
 *              repository_service      =   "pim_catalog.repository.product"
 * )
 * 
 */
class Product {

    use ObjectMetadataTrait;
    use CoreTrait;
    
}
