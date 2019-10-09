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

namespace Splash\Akeneo\Objects;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository as Repository;
use Splash\Akeneo\Services\AttributesManager;
use Splash\Akeneo\Services\CrudService;
use Splash\Akeneo\Services\LocalesManager;
use Splash\Bundle\Models\AbstractStandaloneObject;

/**
 * Splash Product Object
 */
class Product extends AbstractStandaloneObject
{
    // Splash Php Core Traits
    use \Splash\Models\Objects\IntelParserTrait;
    use \Splash\Models\Objects\SimpleFieldsTrait;
    use \Splash\Models\Objects\ListsTrait;
    use \Splash\Models\Objects\GenericFieldsTrait;

    // Akeneo Generic Traits
    use Core\ObjectMetadataTrait;

    // Akeneo Products Traits
    use Product\CrudTrait;
    use Product\CoreTrait;
    use Product\LabelTrait;
    use Product\AttributesTrait;
    use Product\VariantsTrait;
    use Product\ObjectsListTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     *  Object Disable Flag. Uncomment this line to Override this flag and disable Object.
     */
//    protected static    $DISABLED        =  True;

    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME = "Product";

    /**
     *  Object Description (Translated by Module).
     */
    protected static $DESCRIPTION = 'Akeneo Product Object';

    /**
     *  Object Icon (FontAwesome or Glyph ico tag).
     */
    protected static $ICO = 'fa fa-product-hunt';

    /**
     * @var CrudService
     */
    protected $crud;

    /**
     * @var AttributesManager
     */
    protected $attr;

    /**
     * @var LocalesManager
     */
    protected $locales;

    /**
     * Service Constructor
     *
     * @param Repository     $variants
     * @param LocalesManager $locales
     */
    public function __construct(Repository $variants, CrudService $crudService, AttributesManager $attr, LocalesManager $locales)
    {
        //====================================================================//
        // Link to Product Variants Repository
        $this->repository = $variants;
        //====================================================================//
        // Link to Splash Akeneo Products Crud Manager
        $this->crud = $crudService;
        //====================================================================//
        // Link to Splash Akeneo Products Attributes Manager
        $this->attr = $attr->setup(
            $this->getParameter("channel", "ecommerce"),
            $this->getParameter("currency", "EUR")
        );
        //====================================================================//
        // Store Availables Languages
        $this->locales = $locales->setDefault($this->getParameter("locale", "en_US"));
    }
}
