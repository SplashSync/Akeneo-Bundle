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

namespace Splash\Akeneo\Objects\Product\Variants;

use Splash\Core\SplashCore as Splash;

trait CoreTrait
{
    //====================================================================//
    // PRODUCT VARIANT CORE INFOS
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     */
    public function buildVariantCoreFields()
    {
        //====================================================================//
        // PRODUCTS VARIANT METADATA
        //====================================================================//

        //====================================================================//
        // Product SKU
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("variant")
            ->Name("Is Variant")
            ->group("Metadata")
            ->isListed()
            ->isReadOnly();

        //====================================================================//
        // Product Variation Parent Link
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("parent")
            ->Name("Parent Model ID")
            ->Group("Metadata")
            ->MicroData("http://schema.org/Product", "isVariationOf")
            ->isReadOnly();

        //====================================================================//
        // CHILD PRODUCTS INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Product Variation List - Product Link
        $this->fieldsFactory()->Create((string) self::objects()->Encode("Product", SPL_T_ID))
            ->Identifier("id")
            ->Name("Variants")
            ->InList("variants")
            ->MicroData("http://schema.org/Product", "Variants")
            ->isNotTested();

        //====================================================================//
        // Product Variation List - Product SKU
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("sku")
            ->Name("Variant SKU")
            ->InList("variants")
            ->MicroData("http://schema.org/Product", "VariationName")
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    public function getVariantCoreFields(string $key, string $fieldName)
    {
        switch ($fieldName) {
            //====================================================================//
            // Variant Readings
            case 'variant':
                $this->getGenericBool($fieldName);

                break;
            case 'parent':
                $this->out[$fieldName] = $this->variants->getParentModelId($this->object);

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getVariantChildsFields($key, $fieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "variants", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Load Product Variants
        $variants = $this->variants->getVariantsList($this->object);       
        foreach ($variants as $index => $attr) {
            //====================================================================//
            // SKIP Current Variant When in PhpUnit/Travis Mode
            if (!$this->isAllowedVariantChild($attr)) {
                continue;
            }
            //====================================================================//
            // Add Variant Infos
            if (isset($attr[$fieldId])) {
                self::lists()->insert($this->out, "variants", $fieldId, $index, $attr[$fieldId]);
            }
        }
        unset($this->in[$key]);
        //====================================================================//
        // Sort Variants by Code
        ksort($this->out["variants"]);
    }

    //====================================================================//
    // Fields Writting Functions
    //====================================================================//
    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setVariantsCoreFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'variants':
                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
    
    //====================================================================//
    // PRIVATE - Tooling Functions
    //====================================================================//

    /**
     * Check if Product Variant Should be Listed
     *
     * @param array $attribute Combination Resume Array
     *
     * @return bool
     */
    private function isAllowedVariantChild($attribute)
    {
        //====================================================================//
        // Not in PhpUnit/Travis Mode => Return All
        if (!$this->isDebugMode()) {
            return true;
        }       
        //====================================================================//
        // Travis Mode => Skip Current Product Variant
        if ($attribute["rawId"] != $this->object->getId()) {
            return true;
        }
        //====================================================================//
        // Empty Product Variant Id
        if (empty($attribute["id"])) {
            return true;
        }
        
        return false;
    }
}
