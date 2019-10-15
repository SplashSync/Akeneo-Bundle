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

use ArrayObject;
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
    private static $requiredFields = array(
        "code" => "Attribute Code",
        "value" => "Attribute Value",
    );

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Attributes Fields using FieldFactory
     */
    protected function buildVariantsAttributesFields()
    {
        $groupName = "Variants";
        $this->fieldsFactory()->setDefaultLanguage($this->locales->getDefault());

        //====================================================================//
        // PRODUCT VARIANTS ATTRIBUTES
        //====================================================================//

        //====================================================================//
        // Product Variation Attribute Code (Default Language Only)
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("code")
            ->Name("Variation Code")
            ->InList("attributes")
            ->Group($groupName)
            ->addOption("isLowerCase", true)
            ->MicroData("http://schema.org/Product", "VariantAttributeCode")
            ->isNotTested();
        //====================================================================//
        // PhpUnit/Travis Mode => Force Variation Types
        if ($this->isDebugMode()) {
            $this->fieldsFactory()->addChoice("color", "Color");
        }

        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Product Variation Attribute Name
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("label")
                ->Name("Attribute Name")
                ->Group($groupName)
                ->MicroData("http://schema.org/Product", "VariantAttributeName")
                ->setMultilang($isoLang)
                ->InList("attributes")
                ->isReadOnly()
                ->isNotTested();
        }

        //====================================================================//
        // Product Variation Attribute Value
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("value")
            ->Name("Attribute Value")
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "VariantAttributeValue")
            ->InList("attributes")
            ->isNotTested();
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getVariantsAttributesFields($key, $fieldName)
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
    // Fields Writting Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setVariantsAttributesFields($fieldName, $fieldData)
    {
        //====================================================================//
        // Check is Attribute Field
        if ("attributes" != $fieldName) {
            return;
        }

        //====================================================================//
        // Identify Products Attributes Ids
        foreach ($fieldData as $attrItem) {
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
            // If Variant Attribute => Skip Writting (Done via Variation Attributes)
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
     * @param array|ArrayObject $fieldData Attribute Array
     *
     * @return bool
     */
    protected function isValidAttributeDefinition($fieldData)
    {
        //====================================================================//
        // Check Attribute is Array
        if ((!is_array($fieldData) && !is_a($fieldData, "ArrayObject")) || empty($fieldData)) {
            return false;
        }

        //====================================================================//
        // Check Required Attributes Data are Given
        foreach (static::$requiredFields as $key => $name) {
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
     */
    private function getVariantsAttributesField(string $key, string $fieldId, string $isoLang)
    {
        //====================================================================//
        // Decode Multilang Field Name
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
                    $value = $attribute->getTranslation($isoLang)->getLabel();

                    break;
                case 'value':
                    $value = $this->attr->getData($this->object, $attribute, $isoLang);

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
