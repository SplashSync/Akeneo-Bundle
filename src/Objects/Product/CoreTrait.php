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

namespace Splash\Akeneo\Objects\Product;

use Splash\Core\SplashCore as Splash;

/**
 * Product Core Fields Access
 */
trait CoreTrait
{
    //====================================================================//
    // PRODUCT CORE INFOS
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     */
    public function buildCoreFields()
    {
        //====================================================================//
        // Product SKU
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("identifier")
            ->Name("Product SKU")
            ->MicroData("http://schema.org/Product", "model")
            ->isListed()
            ->isReadOnly();

        //====================================================================//
        // Active => Product Is Enables & Visible
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("enabled")
            ->Name("Enabled")
            ->MicroData("http://schema.org/Product", "offered")
            ->isListed();

        //====================================================================//
        // Product Familly
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("family_code")
            ->Name("Family Code")
            ->Group("Metadata")
            ->addChoices($this->variants->getFamilyChoices())
            ->MicroData("http://schema.org/Product", "famillyCode")
            ->isReadOnly();

        //====================================================================//
        // Product Familly Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("family_label")
            ->Name("Family Name")
            ->Group("Metadata")
            ->MicroData("http://schema.org/Product", "famillyName")
            ->isReadOnly();

        //====================================================================//
        // Product Familly Variant
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("family_variant_code")
            ->Name("Family Variant Code")
            ->Group("Metadata")
            ->addChoices($this->variants->getFamilyChoices())
            ->MicroData("http://schema.org/Product", "famillyVariantCode")
            ->isNotTested();

        //====================================================================//
        // PhpUnit/Travis Mode => Force Variation Types
        if ($this->isDebugMode()) {
            $this->fieldsFactory()->addChoice("clothing_color", "Color");
        }
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    public function getCoreFields(string $key, string $fieldName)
    {
        switch ($fieldName) {
            //====================================================================//
            // Variant Readings
            case 'identifier':
                $this->getGeneric($fieldName);

                break;
            case 'enabled':
                $this->getGenericBool($fieldName);

                break;
            case 'family_code':
                $family = $this->object->getFamily();
                $this->out[$fieldName] = $family ? $family->getCode() : null;

                break;
            case 'family_label':
                $family = $this->object->getFamily();
                $this->out[$fieldName] = $family
                    ? $family->getTranslation($this->locales->getDefault())->getLabel()
                    : null;

                break;
            case 'family_variant_code':
                $family = $this->object->getFamilyVariant();
                $this->out[$fieldName] = $family ? $family->getCode() : null;

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setCoreFields($fieldName, $fieldData)
    {
        switch ($fieldName) {
            case 'enabled':
                $this->setGenericBool($fieldName, $fieldData);

                break;
            case 'family_variant_code':
                $family = $this->variants->findFamilyVariantByCode($fieldData);
                if (null === $family) {
                    break;
                }

                $this->object->setFamilyVariant($family);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
