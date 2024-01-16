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

namespace   Splash\Akeneo\Services;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupTranslation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as Product;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertySetter;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AbstractAttribute as Attribute;
use Akeneo\Pim\Structure\Component\Model\AbstractAttribute as Group;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslation;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface as Repository;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver;
use Exception;
use Splash\Akeneo\Models\TypesConverter;
use Splash\Akeneo\Objects\Product\Attributes as SplashAttributes;
use Splash\Akeneo\Services\FilesManager as Files;
use Splash\Client\Splash;
use Splash\Components\FieldsFactory;
use Splash\Models\Objects\FieldsFactoryTrait;

/**
 * Akeneo Product Attribute Data Access
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributesManager
{
    use FieldsFactoryTrait;

    use SplashAttributes\CoreTrait;
    use SplashAttributes\BoolTrait;
    use SplashAttributes\NumberTrait;
    use SplashAttributes\MetricTrait;
    use SplashAttributes\DatesTrait;
    use SplashAttributes\PricesCollectionsTrait;
    use SplashAttributes\SelectTrait;
    use SplashAttributes\MultiSelectTrait;
    use SplashAttributes\ImagesTrait;
    use SplashAttributes\FilesTrait;

    /**
     * Service Constructor
     */
    public function __construct(
        protected PropertySetter      $setter,
        protected Repository        $attributes,
        protected BaseSaver           $optionSaver,
        protected MeasureManager      $measure,
        protected Files               $files,
        protected Configuration       $conf,
        protected LocalesManager      $locales
    ) {
    }

    //====================================================================//
    // PRODUCT ATTRIBUTES FIELDS DEFINITION
    //====================================================================//

    /**
     * Populate Product Object Fields Definition
     *
     * @param FieldsFactory $factory
     */
    public function build(FieldsFactory $factory): void
    {
        //====================================================================//
        // Setup Field Factory
        $this->fieldsFactory()->setDefaultLanguage($this->locales->getDefault());
        //====================================================================//
        // Walk on All Available Attributes
        foreach ($this->getAll() as $attribute) {
            //====================================================================//
            // Remove Prices VAT Fields
            if ($this->isPriceVatField($attribute)) {
                continue;
            }

            //====================================================================//
            // Value is Mono-Lang
            if (!$attribute->isLocalizable()) {
                $this->buildField($factory, $attribute, $this->locales->getDefault());

                continue;
            }
            //====================================================================//
            // Walk on Each Available Languages
            foreach ($this->locales->getAll() as $isoLang) {
                $this->buildField($factory, $attribute, $isoLang);
            }
        }
    }

    //====================================================================//
    // PRODUCT ATTRIBUTES DATA READINGS
    //====================================================================//

    /**
     * Get Field Data from Local Object
     *
     * @param Product $product
     * @param string  $fieldName
     *
     * @throws Exception
     *
     * @return array
     */
    public function get(Product $product, string $fieldName): array
    {
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Check if Attribute Exists
            if (!$this->hasForLocale($fieldName, $isoLang)) {
                continue;
            }
            //====================================================================//
            // Decode Multi-lang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            if (null == $baseFieldName) {
                continue;
            }
            //====================================================================//
            // Detect Virtual Field Name (Bool2String)
            $virtualFieldName = TypesConverter::isVirtual($baseFieldName);
            if (null !== $virtualFieldName) {
                //====================================================================//
                // Load Attribute Virtual Value
                return array(
                    $fieldName => $this->getVirtualData(
                        $product,
                        $this->getByCode($virtualFieldName),
                        $isoLang,
                        false
                    ),
                );
            }

            //====================================================================//
            // Load Attribute Value
            return array(
                $fieldName => $this->getData(
                    $product,
                    $this->getByCode($baseFieldName),
                    $isoLang
                ),
            );
        }

        return array();
    }

    /**
     * Get Field Data from Local Object
     *
     * @param Product   $product
     * @param Attribute $attr
     * @param string    $iso
     *
     * @throws Exception
     *
     * @return null|array|bool|float|int|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getData(Product $product, Attribute $attr, string $iso)
    {
        $attrType = $attr->getType();
        $channel = $this->conf->getChannel();
        //====================================================================//
        // Read & Convert Select Value
        if (TypesConverter::isSelect($attrType)) {
            return $this->getSelectValue($product, $attr, $iso, $channel);
        }
        //====================================================================//
        // Read & Convert Multi-Select Value
        if (TypesConverter::isMultiSelect($attrType)) {
            return $this->getMultiSelectValue($product, $attr, $iso, $channel);
        }
        //====================================================================//
        // Read & Convert Attribute Value
        switch (TypesConverter::toSplash($attr, $this->conf->isCatalogMode())) {
            case SPL_T_BOOL:
                return $this->isBoolValue($product, $attr, $iso, $channel);
            case SPL_T_INT:
            case SPL_T_DOUBLE:
                return TypesConverter::isMetric($attrType)
                    ? $this->getMetricValue($product, $attr, $iso, $channel)
                    : $this->getNumberValue($product, $attr, $iso, $channel);
            case SPL_T_VARCHAR:
            case SPL_T_TEXT:
                return $this->getScalarValue($product, $attr, $iso, $channel);
            case SPL_T_DATE:
                return $this->getDateValue($product, $attr, $iso, $channel);
            case SPL_T_PRICE:
                return $this->getPriceValue($product, $attr, $iso, $channel);
            case SPL_T_FILE:
                return $this->getFileValue($product, $attr, $iso, $channel);
            case SPL_T_IMG:
                return $this->getImageValue($product, $attr, $iso, $channel);
        }

        return null;
    }

    /**
     * Get Field Computed Data from Local Object
     *
     * @param Product   $product
     * @param Attribute $attr
     * @param string    $iso
     * @param bool      $attributeMode
     *
     * @return null|string
     */
    public function getVirtualData(Product $product, Attribute $attr, string $iso, bool $attributeMode): ?string
    {
        $attrType = $attr->getType();
        $channel = $this->conf->getChannel();
        //====================================================================//
        // Read & Convert Bool as String Value
        if (TypesConverter::isBool($attrType)) {
            return $this->getBoolAsStringValue($product, $attr, $iso, $channel, $attributeMode);
        }
        //====================================================================//
        // Read & Convert Metrics as String Value
        if (TypesConverter::isMetric($attrType)) {
            return $this->getMetricAsStringValue($product, $attr, $iso, $channel);
        }
        //====================================================================//
        // Read & Convert Select as Translated
        if (TypesConverter::isSelect($attrType)) {
            return $this->getSelectValueTranslation($product, $attr, $iso, $channel);
        }
        //====================================================================//
        // Read & Convert Multi-Select as Translated
        if (TypesConverter::isMultiSelect($attrType)) {
            return $this->getMultiSelectTranslated($product, $attr, $iso, $channel);
        }

        return null;
    }

    /**
     * Get Field Data from Local Object
     *
     * @param Product $product
     * @param string  $fieldName
     * @param mixed   $fieldData
     *
     * @throws Exception
     *
     * @return bool
     */
    public function set(Product $product, string $fieldName, $fieldData): bool
    {
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Check if Attribute Exists
            if (!$this->hasForLocale($fieldName, $isoLang)) {
                continue;
            }
            //====================================================================//
            // Decode Multi-Lang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            if (null == $baseFieldName) {
                continue;
            }

            //====================================================================//
            // Load Attribute Value
            return $this->setData(
                $product,
                $this->getByCode($baseFieldName),
                $isoLang,
                $fieldData
            );
        }

        return false;
    }

    //====================================================================//
    // PRODUCT ATTRIBUTES CORE FUNCTIONS
    //====================================================================//

    /**
     * Check if Product has Such Attribute Type
     *
     * @param string $fieldName
     *
     * @return bool
     */
    public function has(string $fieldName): bool
    {
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            if ($this->hasForLocale($fieldName, $isoLang)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if Product has Such Attribute Type
     *
     * @param string $fieldName
     * @param string $isoLang
     *
     * @return bool
     */
    public function hasForLocale(string $fieldName, string $isoLang): bool
    {
        //====================================================================//
        // Decode Multilang Field Name
        $baseFieldName = $this->locales->decode($fieldName, $isoLang);
        if (null == $baseFieldName) {
            return false;
        }
        //====================================================================//
        // Detect Virtual Field Name (Bool2String)
        $virtualFieldName = TypesConverter::isVirtual($baseFieldName);
        if (null !== $virtualFieldName) {
            $baseFieldName = $virtualFieldName;
        }
        //====================================================================//
        // Check if Base Field Name Exists
        if (!in_array($baseFieldName, $this->getAllKeys(), true)) {
            return false;
        }

        return true;
    }

    /**
     * Find Product Attribute by Code
     *
     * @param string $attrCode
     *
     * @throws Exception
     *
     * @return Attribute
     */
    public function find(string $attrCode): Attribute
    {
        $this->getAll();

        return $this->getByCode($attrCode);
    }

    /**
     * Get Product Attribute by Code or Label if Lang Code Provided
     *
     * @param string      $codeOrLabel
     * @param null|string $isoCode
     *
     * @throws Exception
     *
     * @return string
     */
    public function findByCodeOrLabel(string $codeOrLabel, ?string $isoCode): string
    {
        /**
         * @var null|array<string, array<string, string>> $attributesLabels
         */
        static $attributesLabels;

        //====================================================================//
        // No ISO Lang Provided => Search by Code
        if (empty($isoCode)) {
            return $this->getByCode($codeOrLabel)->getCode();
        }
        //====================================================================//
        // Ensure Init of Labels cache
        if (!isset($attributesLabels[$isoCode])) {
            $attributesLabels = array();
            foreach ($this->getAll() as $code => $attribute) {
                if ($label = $attribute->getTranslation($isoCode)?->getLabel()) {
                    $attributesLabels[$isoCode][$code] = $label;
                }
            }
        }

        //====================================================================//
        // ISO Lang Provided => Search by Label
        return $this->getByCode(
            (string) array_search($codeOrLabel, $attributesLabels[$isoCode], true)
        )->getCode();
    }

    /**
     * Get All Product Attribute by Type
     *
     * @param string $type Attribute Type
     *
     * @throws Exception
     *
     * @return array<string, Attribute>
     */
    public function findByType(string $type): array
    {
        /** @var null|array<string, Attribute> $attributes */
        static $attributes;

        if (!isset($attributes)) {
            //====================================================================//
            // Init Cache
            $attributes = array();
            //====================================================================//
            // Walk on All Available Attributes
            /** @var Attribute $attribute */
            foreach ($this->attributes->findAll() as $attribute) {
                if ($attribute->getType() === $type) {
                    $attributes[$attribute->getCode()] = $attribute;
                }
            }
        }

        return $attributes;
    }

    /**
     * Get Product Label Attribute Code
     *
     * @param Product $product
     *
     * @return null|string
     */
    public function getLabelAttributeCode(Product $product): ?string
    {
        $family = $product->getFamily();
        if (!$family) {
            return  null;
        }

        $attribute = $family->getAttributeAsLabel();
        if (!$attribute) {
            return  null;
        }

        return $attribute->getCode();
    }

    /**
     * Update Attribute Data with Field Data
     *
     * @param Product   $product
     * @param Attribute $attr
     * @param string    $iso
     * @param mixed     $data
     *
     * @throws Exception
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function setData(Product $product, Attribute $attr, string $iso, $data): bool
    {
        $attrType = $attr->getType();
        $channel = $this->conf->getChannel();
        //====================================================================//
        // Write Select Value
        if (TypesConverter::isSelect($attrType)) {
            return $this->setSelectValue($product, $attr, $iso, $channel, $data);
        }

        //====================================================================//
        // Write Attribute Value
        switch (TypesConverter::toSplash($attr, $this->conf->isCatalogMode())) {
            case SPL_T_BOOL:
                return $this->setBoolValue($product, $attr, $iso, $channel, $data);
            case SPL_T_INT:
            case SPL_T_DOUBLE:
                return TypesConverter::isMetric($attrType)
                    ? $this->setMetricValue($product, $attr, $iso, $channel, $data)
                    : $this->setNumberValue($product, $attr, $iso, $channel, $data);

            case SPL_T_VARCHAR:
            case SPL_T_DATE:
            case SPL_T_TEXT:
                return $this->setCoreValue($product, $attr, $iso, $channel, $data);
            case SPL_T_PRICE:
                return $this->setPriceValue($product, $attr, $iso, $channel, $data);
            case SPL_T_FILE:
            case SPL_T_IMG:
                return $this->setFileValue($product, $attr, $iso, $channel, $data);
        }

        return false;
    }

    /**
     * Generate Single Fields Definition
     *
     * @param FieldsFactory $factory
     * @param Attribute     $attribute
     * @param string        $isoLang
     * @param string        $suffix
     */
    private function buildField(
        FieldsFactory $factory,
        Attribute $attribute,
        string $isoLang,
        string $suffix = ""
    ): void {
        //====================================================================//
        // Get Attribute Type
        $attrType = $attribute->getType();
        //====================================================================//
        // Safety Check => Ensure Type is Known by Splash
        if (!TypesConverter::isKnown($attrType) || TypesConverter::isCore($attrType)) {
            return;
        }
        /** @var Group $group */
        $group = $attribute->getGroup();
        //====================================================================//
        // Collect Names Translations
        /** @var AttributeTranslation $baseAttrTrans */
        $baseAttrTrans = $attribute->getTranslation($this->locales->getDefault());
        /** @var AttributeTranslation $attrTrans */
        $attrTrans = $attribute->getTranslation($isoLang);
        /** @var GroupTranslation $groupTrans */
        $groupTrans = $group->getTranslation($isoLang);
        /** @var GroupTranslation $baseTrans */
        $baseTrans = $group->getTranslation($this->locales->getDefault());
        //====================================================================//
        // Add Field Core Infos
        $factory
            ->create((string) TypesConverter::toSplash($attribute, $this->conf->isCatalogMode()))
            ->identifier($attribute->getCode())
            ->name(empty($attrTrans->getLabel()) ? $attribute->getCode() : $attrTrans->getLabel())
            ->description("[".$groupTrans->getLabel()."] ".$attrTrans->getLabel())
            ->group($baseTrans->getLabel())
        ;
        //====================================================================//
        // Add Field Meta Infos
        $factory->microData("http://schema.org/Product", $baseAttrTrans->getLabel().$suffix);
        //====================================================================//
        // ADD Field Metadata
        $this->addFieldMetadata($factory, $attribute, $isoLang);
        //====================================================================//
        // ADD Virtual Fields to Factory
        $this->buildVirtualField($factory, $attribute, $isoLang);
    }

    /**
     * Complete Fields Definition
     *
     * @param FieldsFactory $factory
     * @param Attribute     $attribute
     * @param string        $isoLang
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function addFieldMetadata(FieldsFactory $factory, Attribute $attribute, string $isoLang): void
    {
        //====================================================================//
        // Get Attribute Type
        $attrType = $attribute->getType();
        //====================================================================//
        // is Field Required ?
        if ($attribute->isRequired()) {
            $factory->isRequired();
        }
        //====================================================================//
        // is Field Primary ?
        if (TypesConverter::isPrimary($attrType)) {
            $factory
                ->microData("http://schema.org/Product", "model")
                ->isPrimary()
            ;
        }
        //====================================================================//
        // Is Field Read Only ?
        if (TypesConverter::isReadOnly($attrType)) {
            $factory->isReadOnly();
        }
        //====================================================================//
        // Is Field A Gallery Image ?
        if (in_array($attribute->getCode(), $this->conf->getImagesCodes(), true)) {
            $factory->isReadOnly();
        }
        //====================================================================//
        // Collect Names Translations
        /** @var AttributeTranslation $baseAttrTrans */
        $baseAttrTrans = $attribute->getTranslation($this->locales->getDefault());
        //====================================================================//
        // Does the Field Have Choices Values ?
        if (TypesConverter::isMetric($attrType)
            || TypesConverter::isSelect($attrType)
            || TypesConverter::isMultiSelect($attrType)) {
            // In Catalog Mode, Main Metadata is for Translations
            if ($this->conf->isCatalogMode()) {
                $factory->microData("http://schema.org/Product", $baseAttrTrans->getLabel()."Code");
            }
            $factory->addChoices($this->getSelectChoices($attribute, $isoLang));
            $factory->isNotTested();
        }
        //====================================================================//
        // is Field Multi-Lang
        if ($attribute->isLocalizable()) {
            $factory->setMultilang($isoLang);
        }
    }

    /**
     * Generate Virtual Fields Definition
     *
     * @param FieldsFactory $factory
     * @param Attribute     $attribute
     * @param string        $isoLang
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function buildVirtualField(FieldsFactory $factory, Attribute $attribute, string $isoLang): void
    {
        //====================================================================//
        // Virtual Fields => Only for Default Language
        if ($isoLang != $this->locales->getDefault()) {
            return;
        }
        $catalogMode = $this->conf->isCatalogMode();
        //====================================================================//
        // Boolean Fields => Add Multi-Lang Varchar Values
        if (TypesConverter::isBool($attribute->getType())) {
            $clonedAttr = clone $attribute;
            $clonedAttr->setCode(TypesConverter::BOOL2STRING.$attribute->getCode());
            $clonedAttr->setType(AttributeTypes::TEXT);
            $clonedAttr->setLocalizable(true);
            foreach ($this->locales->getAll() as $isoLang) {
                $this->buildField($factory, $clonedAttr, $isoLang);
                $factory->isReadOnly();
            }
        }
        //====================================================================//
        // Metrics Fields => Add Varchar Values
        if (TypesConverter::isMetric($attribute->getType())) {
            $clonedAttr = clone $attribute;
            $clonedAttr->setCode(TypesConverter::METRIC2STRING.$attribute->getCode());
            $clonedAttr->setType(AttributeTypes::TEXT);
            $clonedAttr->setLocalizable(true);
            foreach ($this->locales->getAll() as $isoLang) {
                $this->buildField($factory, $clonedAttr, $isoLang, $catalogMode ? "" : "Name");
                $factory->isReadOnly();
            }
        }
        //====================================================================//
        // Select Fields => Add Varchar Values
        if (TypesConverter::isSelect($attribute->getType())) {
            $clonedAttr = clone $attribute;
            $clonedAttr->setCode(TypesConverter::SELECT2TRANS.$attribute->getCode());
            $clonedAttr->setType(AttributeTypes::TEXT);
            $clonedAttr->setLocalizable(true);
            foreach ($this->locales->getAll() as $isoLang) {
                // In Catalog Mode, Virtual Translations is Main Metadata
                $this->buildField($factory, $clonedAttr, $isoLang, $catalogMode ? "" : "Name");
                $factory->isReadOnly();
            }
        }
        //====================================================================//
        // Multi-Select Fields => Add Varchar Values
        if (TypesConverter::isMultiSelect($attribute->getType())) {
            $clonedAttr = clone $attribute;
            $clonedAttr->setCode(TypesConverter::MULTI2TRANS.$attribute->getCode());
            $clonedAttr->setType(AttributeTypes::TEXT);
            $clonedAttr->setLocalizable(true);
            foreach ($this->locales->getAll() as $isoLang) {
                $this->buildField($factory, $clonedAttr, $isoLang, $catalogMode ? "" : "Name");
                $factory->isReadOnly();
            }
        }
    }

    /**
     * Get List of All Attributes with Simple Caching
     *
     * @return array<string, Attribute>
     */
    private function getAll(): array
    {
        /** @var null|array<string, Attribute> $attributes */
        static $attributes;

        if (!isset($attributes)) {
            //====================================================================//
            // Init Cache
            $attributes = array();
            //====================================================================//
            // Walk on All Available Attributes
            /** @var Attribute $attribute */
            foreach ($this->attributes->findAll() as $attribute) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Get List of All Attributes Keys with Simple Caching
     *
     * @return string[]
     */
    private function getAllKeys(): array
    {
        /** @var null|string[] $attributesKeys */
        static $attributesKeys;

        if (!isset($attributesKeys)) {
            //====================================================================//
            // Init Cache
            $attributesKeys = array();
            //====================================================================//
            // Walk on All Available Attributes
            foreach ($this->getAll() as $attribute) {
                $attributesKeys[] = $attribute->getCode();
            }
        }

        return $attributesKeys;
    }

    /**
     * Get Product Attribute by Code
     *
     * @param string $attrCode
     *
     * @throws Exception
     *
     * @return Attribute
     */
    private function getByCode(string $attrCode): Attribute
    {
        $attributes = self::getAll();
        if (!isset($attributes[$attrCode])) {
            throw new Exception(sprintf("You try to load an unknown attribute: %s", $attrCode));
        }

        return $attributes[$attrCode];
    }
}
