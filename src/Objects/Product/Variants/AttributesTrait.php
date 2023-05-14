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

use Akeneo\Pim\Structure\Component\Model\AttributeTranslation;
use Splash\Core\SplashCore      as Splash;

/**
 * Prestashop Product Variants Attributes Data Access
 */
trait AttributesTrait
{
    /**
     * List of Required Attributes Fields
     *
     * @var array
     */
    private static array $requiredFields = array(
        "code" => "Attribute Code",
        "value" => "Attribute Value",
    );

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Attributes Fields using FieldFactory
     *
     * @return void
     */
    protected function buildVariantsAttributesFields(): void
    {
        $groupName = "Variants";
        $this->fieldsFactory()->setDefaultLanguage($this->locales->getDefault());

        //====================================================================//
        // PRODUCT VARIANTS ATTRIBUTES
        //====================================================================//

        //====================================================================//
        // Product Variation Attribute Code (Default Language Only)
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("code")
            ->name("Variation Code")
            ->inList("attributes")
            ->group($groupName)
            ->addOption("isLowerCase")
            ->microData("http://schema.org/Product", "VariantAttributeCode")
            ->isNotTested()
        ;
        //====================================================================//
        // PhpUnit/Travis Mode => Force Variation Types
        if (Splash::isTravisMode()) {
            $this->fieldsFactory()->addChoice("color", "Color");
        }

        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Product Variation Attribute Name
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("label")
                ->name("Attr. Name")
                ->group($groupName)
                ->microData("http://schema.org/Product", "VariantAttributeName")
                ->setMultilang($isoLang)
                ->inList("attributes")
                ->isReadOnly()
                ->isNotTested()
            ;
        }
        //====================================================================//
        // Product Variation Attribute Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("value")
            ->name("Attr. Value Code")
            ->group($groupName)
            ->microData(
                "http://schema.org/Product",
                Splash::isTravisMode() ? "VariantAttributeValue" :  "VariantAttributeValueCode"
            )
            ->inList("attributes")
            ->isNotTested()
        ;
        //====================================================================//
        // Product Variation Attribute Value
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Product Variation Attribute Name
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("value_label")
                ->name("Attr. Value")
                ->group($groupName)
                ->microData(
                    "http://schema.org/Product",
                    Splash::isTravisMode() ? "VariantAttributeValueLabel" :  "VariantAttributeValue"
                )
                ->setMultilang($isoLang)
                ->inList("attributes")
                ->isReadOnly()
            ;
        }
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getVariantsAttributesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "attributes", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Walk on Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            $this->getVariantsAttributesField($key, $fieldId, $isoLang);
        }
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param null|array  $fieldData Field Data
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setVariantsAttributesFields(string $fieldName, ?array $fieldData): void
    {
        //====================================================================//
        // Check is Attribute Field
        if ("attributes" != $fieldName) {
            return;
        }

        //====================================================================//
        // Identify Products Attributes Ids
        foreach ($fieldData ?? array() as $attrItem) {
            //====================================================================//
            // Check Product Attributes are Valid
            if (!$this->isValidAttributeDefinition($attrItem)) {
                continue;
            }
            //====================================================================//
            // Safety Check => Verify if FieldName is An Attribute Type
            if (!$this->attr->has($attrItem["code"])) {
                continue;
            }
            //====================================================================//
            // If Variant Attribute => Skip Writing (Done via Variation Attributes)
            if (!$this->variants->isVariantAttribute($this->object, $attrItem["code"])) {
                continue;
            }
            //====================================================================//
            // Read Data from Attributes Service
            $this->attr->set($this->object, $attrItem["code"], $attrItem["value"]);
        }

        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // Tooling Functions
    //====================================================================//

    /**
     * Check if Attribute Array is Valid for Writing
     *
     * @param array $fieldData Attribute Array
     *
     * @return bool
     */
    protected function isValidAttributeDefinition(array $fieldData): bool
    {
        //====================================================================//
        // Check Attribute is Array
        if (empty($fieldData)) {
            return false;
        }
        //====================================================================//
        // Check Required Attributes Data are Given
        foreach (self::$requiredFields as $key => $name) {
            if (!isset($fieldData[$key])) {
                return Splash::log()->errTrace("Product ".$name." is Missing.");
            }
            if (empty($fieldData[$key]) || !is_scalar($fieldData[$key])) {
                return Splash::log()->errTrace("Product ".$name." is Missing.");
            }
        }

        return true;
    }

    //====================================================================//
    // PRIVATE - Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key     Input List Key
     * @param string $fieldId Field Identifier / Name
     * @param string $isoLang Splash ISO Language Code
     *
     * @return void
     */
    private function getVariantsAttributesField(string $key, string $fieldId, string $isoLang): void
    {
        //====================================================================//
        // Decode Multi-Lang Field Name
        $baseFieldName = $this->locales->decode($fieldId, $isoLang);
        if (null == $baseFieldName) {
            return;
        }
        //====================================================================//
        // Walk on Product Attributes
        foreach ($this->variants->getVariantAttributes($this->object) as $index => $attrCode) {
            //====================================================================//
            // Load Variation Attribute
            $attribute = $this->attr->find($attrCode);

            //====================================================================//
            // Read Attribute Value
            switch ($baseFieldName) {
                case 'code':
                    $value = $attrCode;

                    break;
                case 'label':
                    /** @var AttributeTranslation $translation */
                    $translation = $attribute->getTranslation($isoLang);
                    $value = $translation->getLabel();

                    break;
                case 'value':
                    $value = $this->attr->getData($this->object, $attribute, $isoLang);

                    break;
                case 'value_label':
                    $value = $this->attr->getVirtualData($this->object, $attribute, $isoLang, true);

                    break;
                default:
                    return;
            }

            self::lists()->insert($this->out, "attributes", $fieldId, $index, $value);
        }
        unset($this->in[$key]);
        //====================================================================//
        // Sort Attributes by Code
        ksort($this->out["attributes"]);
    }
}
