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

namespace Splash\Akeneo\Objects\Product\Variants;

use Splash\Client\Splash;

trait CoreTrait
{
    //====================================================================//
    // PRODUCT VARIANT CORE INFOS
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     */
    public function buildVariantCoreFields(): void
    {
        //====================================================================//
        // PRODUCTS VARIANT METADATA
        //====================================================================//

        //====================================================================//
        // Product SKU
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("variant")
            ->name("Is Variant")
            ->group("Metadata")
            ->isListed()
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Variation Parent Link
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("parent")
            ->name("Parent Model ID")
            ->group("Metadata")
            ->microData("http://schema.org/Product", "isVariationOf")
            ->isReadOnly()
        ;
        //====================================================================//
        // CHILD PRODUCTS INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Product Variation List - Product Link
        $this->fieldsFactory()->create((string) self::objects()->Encode("Product", SPL_T_ID))
            ->identifier("id")
            ->name("Variants")
            ->inList("variants")
            ->microData("http://schema.org/Product", "Variants")
            ->isNotTested()
        ;
        //====================================================================//
        // Product Variation List - Product SKU
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("sku")
            ->name("Variant SKU")
            ->inList("variants")
            ->microData("http://schema.org/Product", "VariationName")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    public function getVariantCoreFields(string $key, string $fieldName): void
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
     *
     * @return void
     */
    protected function getVariantChildFields(string $key, string $fieldName): void
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
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setVariantsCoreFields(string $fieldName, $fieldData)
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
    private function isAllowedVariantChild(array $attribute): bool
    {
        //====================================================================//
        // Not in PhpUnit/Travis Mode => Return All
        if (!Splash::isTravisMode()) {
            return true;
        }
        //====================================================================//
        // Travis Mode => Skip Current Product Variant
        if ($attribute["rawId"] != $this->object->getUuid()->toString()) {
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
