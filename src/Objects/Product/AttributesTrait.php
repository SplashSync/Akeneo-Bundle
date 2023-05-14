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

namespace Splash\Akeneo\Objects\Product;

use Exception;
use Splash\Client\Splash;

/**
 * Access to Local Product Attributes Fields
 */
trait AttributesTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    public function buildAttributeFields(): void
    {
        //====================================================================//
        // Ensure Service Configuration
        $this->ensureSetup();

        $this->attr->build($this->fieldsFactory());
    }

    /**
     * Read requested Field
     *
     * @param string $key Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     *
     * @throws Exception
     */
    public function getAttributeFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Safety Check => Verify if FieldName is An Attribute Type
        if (!$this->attr->has($fieldName)) {
            return;
        }
        //====================================================================//
        // Read Data from Attributes Service
        $this->out = array_merge_recursive(
            $this->out,
            $this->attr->get($this->object, $fieldName)
        );
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed $fieldData Field Data
     *
     * @return void
     * @throws Exception
     */
    protected function setAttributeFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Safety Check => Verify if FieldName is An Attribute Type
        if (!$this->attr->has($fieldName)) {
            return;
        }
        //====================================================================//
        // If Variant Attribute => Skip Writing (Done via Variation Attributes)
        if (!$this->variants->isVariantAttribute($this->object, $fieldName)) {
            //====================================================================//
            // Write Data from Attributes Service
            if (!$this->attr->set($this->object, $fieldName, $fieldData)) {
                return;
            }
        }

        unset($this->in[$fieldName]);
    }
}
