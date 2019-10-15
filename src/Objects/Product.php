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
use Splash\Akeneo\Services\AttributesManager as Attributes;
use Splash\Akeneo\Services\CrudService as Crud;
use Splash\Akeneo\Services\LocalesManager as Locales;
use Splash\Akeneo\Services\SecurityService as Security;
use Splash\Akeneo\Services\VariantsManager as Variants;
use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Client\Splash;

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
    use Product\VariantsTrait;
    use Product\AttributesTrait;
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
     * @var Crud
     */
    protected $crud;

    /**
     * @var Attributes
     */
    protected $attr;

    /**
     * @var Variants
     */
    protected $variants;

    /**
     * @var Locales
     */
    protected $locales;

    /**
     * @var Security
     */
    protected $security;

    /**
     * Service Constructor
     *
     * @param Repository     $repository
     * @param LocalesManager $locales
     */
    public function __construct(Repository $repository, Crud $crudService, Attributes $attr, Variants $variants, Locales $locales, Security $security)
    {
        //====================================================================//
        // Link to Product Variants Repository
        $this->repository = $repository;
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
        // Link to Splash Akeneo Products Variants Manager
        $this->variants = $variants;
        //====================================================================//
        // Store Availables Languages
        $this->locales = $locales->setDefault($this->getParameter("locale", "en_US"));
        //====================================================================//
        // Link to Splash Akeneo Security Service
        $this->security = $security;
        //====================================================================//
        // Ensure User Login
        if ((defined('SPLASH_SERVER_MODE') && !empty(SPLASH_SERVER_MODE)) || $this->isDebugMode()) {
            $this->security->ensureSessionUser($this->getParameter("username", "admin"));
        }
    }

    /**
     * Check if System is in Debug Mode
     *
     * @return bool
     */
    protected function isDebugMode(): bool
    {
        //====================================================================//
        // Not in PhpUnit/Travis Mode => Return All
        $travisServer = Splash::input('SPLASH_TRAVIS');
        $travisConfig = $this->getParameter("travis", false);
        if (empty($travisServer) && empty($travisConfig)) {
            return false;
        }
        Splash::log()->deb("Akeneo Works in Debug Mode...");

        return true;
    }
}
