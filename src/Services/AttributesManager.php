<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
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
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AbstractAttribute as Attribute;
use Akeneo\Pim\Structure\Component\Model\AbstractAttribute as Group;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslation;
use Exception;
use Splash\Akeneo\Models\TypesConverter;
use Splash\Akeneo\Services\FilesManager as Files;
use Splash\Components\FieldsFactory;
use Splash\Core\SplashCore as Splash;

/**
 * Akeneo Product Attribute Data Access
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AttributesManager
{
    use \Splash\Models\Objects\FieldsFactoryTrait;

    use \Splash\Akeneo\Objects\Product\Attributes\CoreTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\BoolTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\NumberTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\MetricTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\DatesTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\PricesCollectionsTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\SelectTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\ImagesTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\FilesTrait;

    /**
     * @var PropertySetter
     */
    protected $setter;

    /**
     * @var Files
     */
    protected $files;

    /**
     * Default Scope Code
     *
     * @var string
     */
    private $scope;

    /**
     * Default Currency Code
     *
     * @var string
     */
    private $currency;

    /**
     * Attributes Repository
     *
     * @var AttributeRepository
     */
    private $attrRep;

    /**
     * Attributes Cache
     *
     * @var array
     */
    private static $attributes;

    /**
     * Attributes Keys Cache
     *
     * @var array
     */
    private static $attributesKeys;

    /**
     * @var LocalesManager
     */
    private $locales;

    /**
     * Service Constructor
     *
     * @param PropertySetter      $setter
     * @param AttributeRepository $attributes
     * @param Files               $files
     * @param LocalesManager      $locales
     */
    public function __construct(PropertySetter $setter, AttributeRepository $attributes, Files $files, LocalesManager $locales)
    {
        //====================================================================//
        // Link to Akeneo Product Fields Setter
        $this->setter = $setter;
        //====================================================================//
        // Link to Akeneo Product Attributes Repository
        $this->attrRep = $attributes;
        //====================================================================//
        // Link to Splash Akeneo Files Manager
        $this->files = $files;
        //====================================================================//
        // Link to Splash Locales Manager
        $this->locales = $locales;
    }

    /**
     * Configure Default Channel
     *
     * @param string $scope
     * @param string $currency
     *
     * @return self
     */
    public function setup(string $scope, string $currency): self
    {
        $this->scope = $scope;
        $this->currency = $currency;

        return $this;
    }

    /**
     * Configure Default Channel
     *
     * @return string
     */
    public function getChannel(): string
    {
        return $this->scope;
    }

    /**
     * Configure Default Currency
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
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
        // Walk on All Available Attributes
        /** @var Attribute $attribute */
        foreach ($this->getAll() as $attribute) {
            //====================================================================//
            // Remove Prices VAT Fields
            if ($this->isPriceVatField($attribute)) {
                continue;
            }

            //====================================================================//
            // Value is Monolanguage
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
            // Decode Multilang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            if (null == $baseFieldName) {
                continue;
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
     * @return null|array|bool|float|int|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getData(Product $product, Attribute $attr, string $iso)
    {
        $attrType = $attr->getType();
        //====================================================================//
        // Read & Convert Attribute Value
        if (TypesConverter::isSelect($attrType)) {
            return $this->getSelectValue($product, $attr, $iso, $this->getChannel());
        }
        //====================================================================//
        // Read & Convert Attribute Value
        switch (TypesConverter::toSplash($attr)) {
            case SPL_T_BOOL:
                return $this->getBoolValue($product, $attr, $iso, $this->getChannel());
            case SPL_T_INT:
            case SPL_T_DOUBLE:
                return TypesConverter::isMetric($attrType)
                    ? $this->getMetricValue($product, $attr, $iso, $this->getChannel())
                    : $this->getNumberValue($product, $attr, $iso, $this->getChannel());
            case SPL_T_VARCHAR:
            case SPL_T_TEXT:
                return $this->getCoreValue($product, $attr, $iso, $this->getChannel());
            case SPL_T_DATE:
                return $this->getDateValue($product, $attr, $iso, $this->getChannel());
            case SPL_T_PRICE:
                return $this->getPriceValue($product, $attr, $iso, $this->getChannel());
            case AttributeTypes::OPTION_SIMPLE_SELECT:
                return $this->getSelectValue($product, $attr, $iso, $this->getChannel());
            case SPL_T_FILE:
                return array();
            case SPL_T_IMG:
                return $this->getImageValue($product, $attr, $iso, $this->getChannel());
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
            // Decode Multilang Field Name
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
     * @return Attribute
     */
    public function find(string $attrCode): Attribute
    {
        $this->getAll();

        return $this->getByCode($attrCode);
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
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function setData(Product $product, Attribute $attr, string $iso, $data): bool
    {
        $attrType = $attr->getType();

        //====================================================================//
        // Read & Convert Attribute Value
        if (TypesConverter::isSelect($attrType)) {
            return $this->setSelectValue($product, $attr, $iso, $this->getChannel(), $data);
        }

        //====================================================================//
        // Write Attribute Value
        switch (TypesConverter::toSplash($attr)) {
            case SPL_T_BOOL:
                return $this->setBoolValue($product, $attr, $iso, $this->getChannel(), $data);
            case SPL_T_INT:
            case SPL_T_DOUBLE:
                return TypesConverter::isMetric($attrType)
                    ? $this->setMetricValue($product, $attr, $iso, $this->getChannel(), $data)
                    : $this->setNumberValue($product, $attr, $iso, $this->getChannel(), $data);

            case SPL_T_VARCHAR:
            case SPL_T_DATE:
            case SPL_T_TEXT:
                return $this->setCoreValue($product, $attr, $iso, $this->getChannel(), $data);
            case SPL_T_PRICE:
                return $this->setPriceValue($product, $attr, $iso, $this->getChannel(), $data);
            case SPL_T_FILE:
            case SPL_T_IMG:
                return $this->setFileValue($product, $attr, $iso, $this->getChannel(), $data);
        }

        return false;
    }

    /**
     * Generate Single Fields Definition
     *
     * @param FieldsFactory $factory
     * @param Attribute     $attribute
     * @param string        $isoLang
     */
    private function buildField(FieldsFactory $factory, Attribute $attribute, string $isoLang): void
    {
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
        /** @var AttributeTranslation $attrTrans */
        $attrTrans = $attribute->getTranslation($isoLang);
        /** @var GroupTranslation $groupTrans */
        $groupTrans = $group->getTranslation($isoLang);
        /** @var GroupTranslation $baseTrans */
        $baseTrans = $group->getTranslation($this->locales->getDefault());
        //====================================================================//
        // Add Field Core Infos
        $factory
            ->create((string) TypesConverter::toSplash($attribute))
            ->identifier($attribute->getCode())
            ->name($attrTrans->getLabel())
            ->description("[".$groupTrans->getLabel()."] ".$attrTrans->getLabel())
            ->group($baseTrans->getLabel())
        ;
        //====================================================================//
        // is Field Required ?
        if ($attribute->isRequired()) {
            $factory->isRequired();
        }
        //====================================================================//
        // is Field read Only ?
        if (TypesConverter::isReadOnly($attrType)) {
            $factory->isReadOnly();
        }
        //====================================================================//
        // Does the Field Have Choices Values ?
        if (TypesConverter::isSelect($attrType)) {
            $factory->addChoices($this->getSelectChoices($attribute, $isoLang));
            $factory->isNotTested();
        }
        //====================================================================//
        // is Field Multilang
        if ($attribute->isLocalizable()) {
            $factory->setMultilang($isoLang);
        }
    }

    /**
     * Get List of All Attributes with Simple Caching
     *
     * @return array
     */
    private function getAll(): array
    {
        if (!isset(static::$attributes)) {
            //====================================================================//
            // Init Cache
            static::$attributes = array();
            //====================================================================//
            // Walk on All Available Attributes
            /** @var Attribute $attribute */
            foreach ($this->attrRep->findAll() as $attribute) {
                static::$attributes[$attribute->getCode()] = $attribute;
            }
        }

        return static::$attributes;
    }

    /**
     * Get List of All Attributes Keys with Simple Caching
     *
     * @return array
     */
    private function getAllKeys(): array
    {
        if (!isset(static::$attributesKeys)) {
            //====================================================================//
            // Init Cache
            static::$attributesKeys = array();
            //====================================================================//
            // Walk on All Available Attributes
            /** @var Attribute $attribute */
            foreach ($this->getAll() as $attribute) {
                static::$attributesKeys[] = $attribute->getCode();
            }
        }

        return static::$attributesKeys;
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
        if (!isset(static::$attributes[$attrCode])) {
            throw new Exception(sprintf("You try to load an unknown attibute: %s", $attrCode));
        }

        return static::$attributes[$attrCode];
    }
}
