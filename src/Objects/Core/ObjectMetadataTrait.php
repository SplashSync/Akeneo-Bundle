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

namespace Splash\Akeneo\Objects\Core;

/**
 * Acces to Objects Generic Metadata
 */
trait ObjectMetadataTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    public function buildDatesFields()
    {
        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->Identifier("created")
            ->Name("Creation Date")
            ->MicroData("http://schema.org/DataFeedItem", "dateCreated")
            ->group("Metadata")
            ->isReadOnly();

        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->Identifier("updated")
            ->Name("Updated Date")
            ->MicroData("http://schema.org/DataFeedItem", "dateModified")
            ->group("Metadata")
            ->isListed()
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    public function getDatesFields(string $key, string $fieldName)
    {
        switch ($fieldName) {
            //====================================================================//
            // Variant Readings
            case 'created':
            case 'updated':
                $this->getGenericDateTime($fieldName);

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }
}
