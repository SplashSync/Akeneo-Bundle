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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as AkeneoProduct;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Doctrine\ORM\EntityManagerInterface;
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
    protected static string $name = "Product";

    /**
     * Object Description (Translated by Module).
     *
     * @var string
     */
    protected static string $description = 'Akeneo Product Object';

    /**
     * Object Icon (FontAwesome or Glyph ico tag).
     *
     * @var string
     */
    protected static string $ico = 'fa fa-product-hunt';

    /**
     * Object Synchronization Recommended Configuration
     *
     * @codingStandardsIgnoreStart
     */

    /**
     * @var bool Enable Creation Of New Local Objects when Not Existing
     */
    protected static bool $enablePushCreated = false;

    /**
     * @var bool Enable Update Of Existing Local Objects when Modified Remotly
     */
    protected static bool $enablePushUpdated = false;

    /**
     * @var bool Enable Delete Of Existing Local Objects when Deleted Remotly
     */
    protected static bool $enablePushDeleted = false;

    /**
     * @phpstan-var AkeneoProduct
     */
    protected object $object;

    /**
     * Get Operations Output Buffer
     *
     * @var array
     */
    protected array $out;

    /**
     * @var Repository
     */
    protected Repository $repository;

    /**
     * @var Crud
     */
    protected Crud $crud;

    /**
     * @var Attributes
     */
    protected Attributes $attr;

    /**
     * @var Variants
     */
    protected Variants $variants;

    /**
     * @var Locales
     */
    protected Locales $locales;

    /**
     * @var Files
     */
    protected Files $files;

    /**
     * Service Constructor
     *
     * @param Repository $repository
     * @param Crud       $crudService
     * @param Attributes $attr
     * @param Variants   $variants
     * @param Files      $files
     * @param Locales    $locales
     */
    public function __construct(
        Repository $repository,
        Crud $crudService,
        Attributes $attr,
        Variants $variants,
        Files $files,
        Locales $locales
    ) {
        //====================================================================//
        // Link to Product Variants Repository
        $this->repository = $repository;
        //====================================================================//
        // Link to Splash Akeneo Products Crud Manager
        $this->crud = $crudService;
        //====================================================================//
        // Link to Splash Akeneo Products Attributes Manager
        $this->attr = $attr;
        //====================================================================//
        // Link to Splash Akeneo Products Variants Manager
        $this->variants = $variants;
        //====================================================================//
        // Link to Splash Akeneo Products Files Manager
        $this->files = $files;
        //====================================================================//
        // Store Available Languages
        $this->locales = $locales;
        //====================================================================//
        // Ensure Setup
        $this->ensureSetup();
    }

    /**
     * Ensure Service Configuration
     *
     * @return self
     */
    protected function ensureSetup(): self
    {
        /** @var string $channel */
        $channel = $this->getParameter("channel", "ecommerce");
        /** @var string $currency */
        $currency = $this->getParameter("currency", "EUR");
        /** @var string $locale */
        $locale = $this->getParameter("locale", "en_US");
        /** @var bool $catalogMode */
        $catalogMode = $this->getParameter("catalog_mode", false);
        //====================================================================//
        // Setup Splash Akeneo Products Attributes Manager
        $this->attr->setup($channel, $currency, $catalogMode);
        //====================================================================//
        // Default Language
        $this->locales->setDefault($locale);

        return $this;
    }
}
