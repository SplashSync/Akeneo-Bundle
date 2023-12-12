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

namespace Splash\Akeneo\Objects\Product;

use Akeneo\Pim\Structure\Component\Model\FamilyTranslation;
use Splash\Client\Splash;

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
     *
     * @return void
     */
    public function buildCoreFields(): void
    {
        //====================================================================//
        // Product SKU
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("identifier")
            ->name("Product SKU")
            ->microData("http://schema.org/Product", "identifier")
            ->isListed()
            ->isReadOnly()
        ;
        //====================================================================//
        // Active => Product Is Enables & Visible
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("enabled")
            ->name("Enabled")
            ->microData("http://schema.org/Product", "offered")
            ->isListed()
        ;
        //====================================================================//
        // Product Family
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("family_code")
            ->name("Family Code")
            ->group("Metadata")
            ->addChoices($this->variants->getFamilyChoices())
            ->microData("http://schema.org/Product", "famillyCode")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Family Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("family_label")
            ->name("Family Name")
            ->group("Metadata")
            ->microData("http://schema.org/Product", "famillyName")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Family Variant
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("family_variant_code")
            ->name("Family Variant Code")
            ->group("Metadata")
            ->addChoices($this->variants->getFamilyChoices())
            ->microData("http://schema.org/Product", "famillyVariantCode")
            ->isNotTested()
        ;
        //====================================================================//
        // PhpUnit/Travis Mode => Force Variation Types
        if (Splash::isTravisMode()) {
            $this->fieldsFactory()->addChoice("clothing_color", "Color");
        }
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    public function getCoreFields(string $key, string $fieldName): void
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
                $this->out[$fieldName] = $family?->getCode();

                break;
            case 'family_label':
                $family = $this->object->getFamily();
                $this->out[$fieldName] = null;
                if ($family) {
                    /** @var FamilyTranslation $familyTranslation */
                    $familyTranslation = $family->getTranslation($this->locales->getDefault());
                    $this->out[$fieldName] = $familyTranslation->getLabel();
                }

                break;
            case 'family_variant_code':
                $family = $this->object->getFamilyVariant();
                $this->out[$fieldName] = $family?->getCode();

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string    $fieldName Field Identifier / Name
     * @param null|bool $fieldData Field Data
     *
     * @return void
     */
    protected function setCoreBoolFields(string $fieldName, ?bool $fieldData): void
    {
        switch ($fieldName) {
            case 'enabled':
                $this->setGenericBool($fieldName, $fieldData);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }

    /**
     * Write Given Fields
     *
     * @param string      $fieldName Field Identifier / Name
     * @param null|string $fieldData Field Data
     *
     * @return void
     */
    protected function setCoreFamilyFields(string $fieldName, ?string $fieldData): void
    {
        switch ($fieldName) {
            case 'family_variant_code':
                //====================================================================//
                // No Update Required
                if (!$fieldData || ($this->object->getFamilyVariant() == $fieldData)) {
                    break;
                }
                //====================================================================//
                // Load Family Variant
                $familyVariant = $this->variants->findFamilyVariantByCode($fieldData);
                if ((null === $familyVariant)) {
                    break;
                }
                //====================================================================//
                // Update Product Family Variant
                $this->crud->updateFamilyVariant($this->object, $familyVariant);
                $this->needUpdate();

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
