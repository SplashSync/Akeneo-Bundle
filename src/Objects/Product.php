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

namespace Splash\Akeneo\Objects;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductRepository as Repository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as AkeneoProduct;
use Splash\Akeneo\Configurators\Product\CatalogModeConfigurator;
use Splash\Akeneo\Configurators\Product\LearningModeConfigurator;
use Splash\Akeneo\Services\AttributesManager as Attributes;
use Splash\Akeneo\Services\Configuration;
use Splash\Akeneo\Services\CrudService as Crud;
use Splash\Akeneo\Services\FilesManager as Files;
use Splash\Akeneo\Services\GalleryManager as Gallery;
use Splash\Akeneo\Services\LocalesManager as Locales;
use Splash\Akeneo\Services\VariantsManager as Variants;
use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Client\Splash;
use Splash\Models\FileProviderInterface;
use Splash\Models\Objects\PrimaryKeysAwareInterface;

/**
 * Splash Product Object
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Product extends AbstractStandaloneObject implements FileProviderInterface, PrimaryKeysAwareInterface
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
    use Product\PrimaryTrait;
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
     * Service Constructor
     */
    public function __construct(
        protected Repository $repository,
        protected Crud $crud,
        protected Attributes $attr,
        protected Variants $variants,
        protected Files $files,
        protected Gallery $gallery,
        protected Configuration $configuration,
        protected Locales $locales
    ) {
        //====================================================================//
        // Setup Splash Akeneo Connector
        $this->configuration->setup($this);
    }

    /**
     * {@inheritdoc}
     */
    public function description(): array
    {
        //====================================================================//
        // Setup Splash Akeneo Connector
        $this->configuration->setup($this);
        //====================================================================//
        // Learning Mode Configuration
        if ($this->configuration->isLearningMode()) {
            self::$enablePushCreated = true;
            self::$enablePushUpdated = true;
            self::$enablePushDeleted = true;
            self::$enablePullCreated = false;
            self::$enablePullUpdated = false;
            self::$enablePullDeleted = false;
        }
        //====================================================================//
        // Catalog Mode Configuration
        if ($this->configuration->isCatalogMode()) {
            self::$allowPushCreated = false;
            self::$allowPushUpdated = false;
            self::$allowPushDeleted = false;
        }

        return parent::description();
    }

    /**
     * Register Configurators
     *
     * @return void
     */
    protected function buildConfiguratorFields(): void
    {
        //====================================================================//
        // Setup Splash Akeneo Connector
        $this->configuration->setup($this);
        //====================================================================//
        // Learning Mode Configurator
        if ($this->configuration->isLearningMode()) {
            Splash::log()->war("Learning Mode is Enabled. Configuration is modified.");
            $this->fieldsFactory()->registerConfigurator(
                "Product",
                new LearningModeConfigurator()
            );

            return;
        }
        //====================================================================//
        // Catalog Mode Configurator
        if ($this->configuration->isCatalogMode()) {
            Splash::log()->war("Catalog Mode is Enabled. Configuration is modified.");
            $this->fieldsFactory()->registerConfigurator(
                "Product",
                new CatalogModeConfigurator()
            );
        }
    }
}
