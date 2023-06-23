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

namespace Splash\Akeneo\Objects\Core;

/**
 * Override Default Configuration for Special Modes
 */
trait ObjectDescriptionTrait
{
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
}
