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

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface as Repository;
use Akeneo\Category\Infrastructure\Component\Manager\PositionResolver;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface as AkeneoCategory;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface as Factory;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface as Remover;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface as Saver;
use Splash\Akeneo\Services\Configuration;
use Splash\Akeneo\Services\FilesManager as Files;
use Splash\Akeneo\Services\LocalesManager as Locales;
use Splash\Bundle\Models\AbstractStandaloneObject;

/**
 * Splash Product Categories
 */
class Category extends AbstractStandaloneObject
{
    //====================================================================//
    // Splash Php Core Traits
    use \Splash\Models\Objects\IntelParserTrait;
    use \Splash\Models\Objects\SimpleFieldsTrait;
    use \Splash\Models\Objects\ListsTrait;
    use \Splash\Models\Objects\GenericFieldsTrait;
    //====================================================================//
    // Akeneo Generic Traits
    use Core\ObjectMetadataTrait;
    use Core\ObjectDescriptionTrait;
    //====================================================================//
    // Category Traits
    use Category\CrudTrait;
    use Category\CoreTrait;
    use Category\ParentTrait;
    use Category\ObjectsListTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * @var string
     */
    protected static string $name = "Product Category";

    /**
     * Object Description (Translated by Module)
     *
     * @var string
     */
    protected static string $description = "Akeneo Product Category";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * @var string
     */
    protected static string $ico = "fa fa-file-text fas fa-hand-holding-usd";

    //====================================================================//
    // Object Synchronization Recommended Configuration
    //====================================================================//

    /**
     * Enable Creation Of New Local Objects when Not Existing
     *
     * @var bool
     */
    protected static bool $enablePushCreated = false;

    /**
     * Enable Update Of Existing Local Objects when Modified Remotely
     *
     * @var bool
     */
    protected static bool $enablePushUpdated = false;

    /**
     * Enable Delete Of Existing Local Objects when Deleted Remotely
     *
     * @var bool
     */
    protected static bool $enablePushDeleted = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @phpstan-var   AkeneoCategory
     *
     * @var object
     */
    protected object $object;

    //====================================================================//
    // Class Constructor
    //====================================================================//

    /**
     * Class Constructor
     */
    public function __construct(
        protected Repository $repository,
        protected Factory $factory,
        protected Saver $saver,
        protected Remover $remover,
        protected PositionResolver $positionResolver,
        protected Files $files,
        protected Configuration $configuration,
        protected Locales $locales
    ) {
        //====================================================================//
        // Setup Splash Akeneo Connector
        $this->configuration->setup($this);
    }
}
