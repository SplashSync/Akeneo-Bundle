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

namespace Splash\Akeneo\Objects;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductRepository as Repository;
use Splash\Akeneo\Services\AttributesManager as Attributes;
use Splash\Akeneo\Services\CrudService as Crud;
use Splash\Akeneo\Services\FilesManager as Files;
use Splash\Akeneo\Services\LocalesManager as Locales;
use Splash\Akeneo\Services\SecurityService as Security;
use Splash\Akeneo\Services\VariantsManager as Variants;
use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Client\Splash;
use Splash\Models\FileProviderInterface;

/**
 * Splash Product Object
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Product extends AbstractStandaloneObject implements FileProviderInterface
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
    use Product\ImagesTrait;
    use Product\CategoriesTrait;
    use Product\AttributesTrait;
    use Product\ObjectsListTrait;
    use Product\FilesTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * @var string
     */
    protected static $NAME = "Product";

    /**
     * Object Description (Translated by Module).
     *
     * @var string
     */
    protected static $DESCRIPTION = 'Akeneo Product Object';

    /**
     * Object Icon (FontAwesome or Glyph ico tag).
     *
     * @var string
     */
    protected static $ICO = 'fa fa-product-hunt';

    /**
     * Object Synchronization Recommended Configuration
     *
     * @codingStandardsIgnoreStart
     */

    /**
     * @var bool Enable Creation Of New Local Objects when Not Existing
     */
    protected static $ENABLE_PUSH_CREATED = false;

    /**
     * @var bool Enable Update Of Existing Local Objects when Modified Remotly
     */
    protected static $ENABLE_PUSH_UPDATED = false;

    /**
     * @var bool Enable Delete Of Existing Local Objects when Deleted Remotly
     */
    protected static $ENABLE_PUSH_DELETED = false;

    /** @codingStandardsIgnoreEnd */

    /**
     * Get Operations Output Buffer
     *
     * @var array
     */
    protected $out;

    /**
     * @var Repository
     */
    protected $repository;

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
     * @var Files
     */
    protected $files;

    /**
     * Service Constructor
     *
     * @param Repository $repository
     * @param Crud       $crudService
     * @param Attributes $attr
     * @param Variants   $variants
     * @param Files      $files
     * @param Locales    $locales
     * @param Security   $security
     */
    public function __construct(
        Repository $repository,
        Crud $crudService,
        Attributes $attr,
        Variants $variants,
        Files $files,
        Locales $locales,
        Security $security
    ) {
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
            $this->getParameter("currency", "EUR"),
            $this->getParameter("catalog_mode", false)
        );
        //====================================================================//
        // Link to Splash Akeneo Products Variants Manager
        $this->variants = $variants;
        //====================================================================//
        // Link to Splash Akeneo Products Files Manager
        $this->files = $files;
        //====================================================================//
        // Store Available Languages
        $this->locales = $locales->setDefault($this->getParameter("locale", "en_US"));
        //====================================================================//
        // Link to Splash Akeneo Security Service
        $this->security = $security;
        //====================================================================//
        // Ensure User Login
//        if ((defined('SPLASH_SERVER_MODE') && !empty(SPLASH_SERVER_MODE)) || $this->isDebugMode()) {
//            $this->security->ensureSessionUser($this->getParameter("username", "admin"));
//        }
    }

    /**
     * Ensure Service Configuration
     *
     * @return self
     */
    protected function ensureSetup(): self
    {
        //====================================================================//
        // Setup Splash Akeneo Products Attributes Manager
        $this->attr->setup(
            $this->getParameter("channel", "ecommerce"),
            $this->getParameter("currency", "EUR"),
            $this->getParameter("catalog_mode", false)
        );
        //====================================================================//
        // Default Language
        $this->locales->setDefault($this->getParameter("locale", "en_US"));

        return $this;
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
