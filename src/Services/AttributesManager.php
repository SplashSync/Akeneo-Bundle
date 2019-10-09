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

/**
 * @abstract    Akeneo Bundle Data Transformer for Splash Bundle
 *
 * @author      B. Paquier <contact@splashsync.com>
 */

namespace   Splash\Akeneo\Services;

use Splash\Core\SplashCore as Splash;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Builder\ProductBuilder;
use Pim\Component\Catalog\Model\AttributeInterface as Attribute;
use Pim\Component\Catalog\Model\ProductInterface as Product;
use Pim\Component\Catalog\Updater\PropertySetter;
use Splash\Akeneo\Models\TypesConverter;
use Splash\Bundle\Annotation\Field;
use Splash\Components\FieldsFactory;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Exception;

class AttributesManager
{
    use \Splash\Models\Objects\FieldsFactoryTrait;

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
    
    use \Splash\Akeneo\Objects\Product\Attributes\CoreTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\BoolTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\IntegerTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\MetricTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\DatesTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\PricesCollectionsTrait;
    use \Splash\Akeneo\Objects\Product\Attributes\SelectTrait;
    
//    use \Splash\Akeneo\Objects\Product\CRUDTrait;
//    use \Splash\Akeneo\Objects\Product\CoreTrait;
//    use \Splash\Akeneo\Objects\Product\DatesTrait;
//    use \Splash\Akeneo\Objects\Product\OptionsTrait;
//    use \Splash\Akeneo\Objects\Product\ImagesTrait;
//    use \Splash\Akeneo\Objects\Product\PricesCollectionsTrait;
//
//    /**
//     * @var EntityManagerInterface
//     */
//    private $EntityManager;
//
//    /**
//     * @var RouterInterface
//     */
//    private $Router;
//
//    /**
//     * @var ProductBuilder
//     */
//    private $Builder;
//
//    /**
//     * @var ObjectUpdaterInterface
//     */
//    private $Updater;
//
//    /**
//     * @var RecursiveValidator
//     */
//    private $Validator;
//

    /**
     * @var PropertySetter
     */
    private $setter;

    /**
     * @var LocalesManager
     */
    private $locales;

//
//    /**
//     * @var array
//     */
//    private $Config;
//
//    /**
//     * @var string
//     */
//    private $Catalog_Storage_Dir;
//

    /**
     * Service Constructor
     *
     * @param PropertySetter $setter
     */
    public function __construct(PropertySetter $setter, AttributeRepository $attributes, LocalesManager $locales)
    {
        //====================================================================//
        // Link to Akeneo Product Fields Setter
        $this->setter = $setter;
        //====================================================================//
        // Link to Akeneo Product Attributes Repository
        $this->attrRep = $attributes;
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

//
//    //====================================================================//
//    // PRODUCT FIELDS ACCESS
//    //====================================================================//
//
//    /**
//     *  @abstract       Export Field Data from Local Object
//     *
//     *  @param  mixed   $Object         Current Local Object
//     *  @param  string  $Id             Splash Field Id
//     *  @param  string  $Type           Splash Field Type
//     *
//     *  @return         mixed           Splash Formated Data
//     */
//    public function exportCore($Object, string $Id, string $Type)
//    {
//        //====================================================================//
//        // Load All Available Attributes
//        $Attribute     =   $this->EntityManager
//                ->getRepository("PimCatalogBundle:Attribute")
//                ->findOneByIdentifier($Id);
//
//        if( empty($Attribute) ) {
//            return parent::exportCore($Object, $Id, $Type);
//        }
//
//        return $this->getAttributeData($Object, $Attribute);
//    }
//
//    /**
//     *  @abstract       Import Field Data to Local Object
//     *
//     *  @param  mixed   $Object         Current Local Object
//     *  @param  string  $Id             Splash Field Id
//     *  @param  string  $Type           Splash Field Type
//     *  @param  mixed   $Data           Field Input Splash Formated Data
//     *
//     *  @return         mixed           Splash Formated Data
//     */
//    public function importCore($Object, string $Id, string $Type, $Data)
//    {
//        //====================================================================//
//        // Load All Available Attributes
//        $Attribute     =   $this->EntityManager
//                ->getRepository("PimCatalogBundle:Attribute")
//                ->findOneByIdentifier($Id);
//
//        if( empty($Attribute) ) {
//            return parent::importCore($Object, $Id, $Type, $Data);
//        }
//
//        return $this->setAttributeData($Object, $Attribute, $Data);
//    }
//
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
        /** @var AttributeInterface $attribute */
        foreach ($this->getAll() as $attribute) {
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

    /**
     * Generate Single Fields Definition
     *
     * @param FieldsFactory $factory
     * @param Attribute     $attribute
     * @param string        $isoLang
     */
    public function buildField(FieldsFactory $factory, Attribute $attribute, string $isoLang): void
    {
        //====================================================================//
        // Get Attribute Type
        $attrType = $attribute->getType();
        //====================================================================//
        // Safety Check => Ensure Type is Known by Splash
        if (!TypesConverter::isKnown($attrType) || TypesConverter::isCore($attrType)) {
            return;
        }
        //====================================================================//
        // Collect Names
        $attrName = $attribute->getTranslation($isoLang)->getLabel();
        $attrGroup = $attribute->getGroup()->getTranslation($isoLang)->getLabel();
        $baseGroup = $attribute->getGroup()->getTranslation($this->locales->getDefault())->getLabel();
        //====================================================================//
        // Add Field Core Infos
        $factory
            ->create(TypesConverter::toSplash($attrType))
            ->identifier($attribute->getCode())
            ->name($attrName)
            ->description("[".$attrGroup."] ".$attrName)
            ->group($baseGroup)
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
        if ( TypesConverter::isSelect($attrType) ) {
            $factory->addChoices($this->getSelectChoices($attribute , $isoLang));
        }
        //====================================================================//
        // is Field Multilang
        if ($attribute->isLocalizable()) {
            $factory->setMultilang($isoLang);
        }
    }

    //====================================================================//
    // PRODUCT ATTRIBUTES DATA READINGS
    //====================================================================//

    

    /**
     * Get Field Data from Local Object
     * 
     * @param Product $product
     * @param string $fieldName
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
            if(!$this->hasForLocale($fieldName, $isoLang)) {
                continue;
            }            
            //====================================================================//
            // Decode Multilang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            if(null == $baseFieldName) {
                continue;
            }
            //====================================================================//
            // Load Attribute Value
            return array($fieldName => $this->getData(
                $product, 
                $this->getByCode($baseFieldName), 
                $isoLang
            ));
        }
        
        return array();
    }
    

    /**
     * Get Field Data from Local Object
     * 
     * @param Product $product
     * @param string $fieldName
     * 
     * @return array
     */
    private function getData(Product $product, Attribute $attr, string $iso)
    {
        $attrType = $attr->getType();
        //====================================================================//
        // Read & Convert Attribute Value
        if ( TypesConverter::isSelect($attrType) )
        {
            return $this->getSelectValue($product, $attr, $iso, $this->getChannel());
        }  
        //====================================================================//
        // Read & Convert Attribute Value
        switch ( TypesConverter::toSplash($attrType) )
        {
            case SPL_T_BOOL:
                return $this->getBoolValue($product, $attr, $iso, $this->getChannel());
                
            case SPL_T_INT:
                return $this->getIntegerValue($product, $attr, $iso, $this->getChannel());
                
            case SPL_T_DOUBLE:
                return $this->getMetricValue($product, $attr, $iso, $this->getChannel());
                
            case SPL_T_VARCHAR:
            case SPL_T_TEXT:
                return $this->getCoreValue($product, $attr, $iso, $this->getChannel());

            case SPL_T_DATE:
                return $this->getDateValue($product, $attr, $iso, $this->getChannel());

            case SPL_T_PRICE:
                return $this->getPriceValue($product, $attr, $iso, $this->getChannel());

            case AttributeTypes::OPTION_SIMPLE_SELECT:
                return $this->getSelectValue($product, $attr, $iso, $this->getChannel());
                
//            case AttributeTypes::FILE:
//                return Null;
//
//            case AttributeTypes::IMAGE:
//                return $this->exportImage($Object, $Attribute);
//

//
//                $attrValue = $this->getCoreValue($product, $attribute, $isoLang, $this->getChannel());
//                return $this->getAttributeValue($Object, $Attribute);


        }  
        
        return null;
    }
//
//    /**
//     *  @abstract       Export Field Data from Local Object
//     *
//     *  @param  ProductInterface    $Object         Akeneo Product Object
//     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
//     *  @param  mixed               $Data           Field Input Splash Formated Data
//     *
//     *
//     *  @return         bool
//     */
//    public function setAttributeData(ProductInterface $Object, AttributeInterface $Attribute, $Data)
//    {
//        //====================================================================//
//        // Read & Convert Attribute Value
//        switch ( $Attribute->getType() )
//        {
//            case AttributeTypes::BOOLEAN:
//                return $this->setAttributeValue($Object, $Attribute, $Data);
//
//            case AttributeTypes::DATE:
//                return $this->setAttributeValue($Object, $Attribute, $Data);
//
//            case AttributeTypes::FILE:
//                return Null;
//
//            case AttributeTypes::IMAGE:
//                return $this->exportImage($Object, $Attribute, $Data);
//
//            case AttributeTypes::OPTION_SIMPLE_SELECT:
//                return $this->importOption($Object, $Attribute, $Data);
//
//            case AttributeTypes::IDENTIFIER:
//            case AttributeTypes::TEXT:
//            case AttributeTypes::TEXTAREA:
//                return $this->setAttributeValue($Object, $Attribute, $Data);
//
//            case AttributeTypes::PRICE_COLLECTION:
//                return $this->importPrice($Object, $Attribute, $Data);
//
//        }
//
//        return Null;
//    }
//
//
//
//    public function getEnabled($Object)
//    {
//        return $Object->isEnabled();
//    }
//
//

//
//    /**
//     *  @abstract    Write Attribute Data with Local & Scope Detection
//     *
//     *  @param  ProductInterface    $Object         Akeneo Product Object
//     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
//     *  @param  mixed               $Data           Field Input Splash Formated Data
//     *
//     *  @return bool
//     */
//    public function setAttributeValue(ProductInterface $Object, AttributeInterface $Attribute, $Data)
//    {
//
//
//        $FieldsValues = array();
//
//        //====================================================================//
//        // Value is Similar for All Langs & All Channels
//        if ( !$Attribute->isScopable() && !$Attribute->isLocalizable() ) {
//            $FieldsValues[$Attribute->getCode()] = [["locale" => null, "scope" => null, "data" => $Data]];
//        }
//
//        //====================================================================//
//        // Value is Channels Specific
//        if ( $Attribute->isScopable() && !$Attribute->isLocalizable() ) {
//            $FieldsValues[$Attribute->getCode()] = [["locale" => null, "scope" => $this->Config["scope"], "data" => $Data]];
//        }
//
//        //====================================================================//
//        // Value is Multilanguage Specific
//        if ( $Attribute->isLocalizable() ) {
//            $Scope  =   $Attribute->isScopable() ? $this->Config["scope"] : Null;
//            $LocalizedValues = [];
//            foreach ($Data as $Locale => $Value) {
//                $LocalizedValues[]  =   ["locale" => $Locale, "scope" => $Scope, "data" => $Value];
//            }
//            $FieldsValues[$Attribute->getCode()] = $LocalizedValues;
//        }
//
//        //====================================================================//
//        // Update Attribute with Error detection
//        try {
//            return $this->Updater->update($Object, [ "values" => $FieldsValues ]);
//        } catch ( \Exception $e) {
//            Splash::Log()->Err($e->getMessage());
//        }
//    }
    
    
    //====================================================================//
    // PRODUCT ATTRIBUTES CORE FUNCTIONS
    //====================================================================//

    /**
     * Check if Product has Such Attribute Type
     *
     * @param string $fieldName
     * @return bool
     */
    public function has(string $fieldName): bool
    {
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            if($this->hasForLocale($fieldName, $isoLang)) {
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
     * @return bool
     */
    public function hasForLocale(string $fieldName, string $isoLang): bool
    {
        //====================================================================//
        // Decode Multilang Field Name
        $baseFieldName = $this->locales->decode($fieldName, $isoLang);          
        if(null == $baseFieldName) {
            return false;
        }
        //====================================================================//
        // Check if Base Field Name Exists
        if(!in_array($baseFieldName, $this->getAllKeys())) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get List of All Attributes with Simple Caching
     * 
     * @return array
     */
    private function getAll(): array
    {
        if(!isset(static::$attributes)) {
            
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
        if(!isset(static::$attributesKeys)) {
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
     * @return Attribute
     * 
     * @throws Exception
     */
    private function getByCode(string $attrCode): Attribute
    {
        if(!isset(static::$attributes[$attrCode])) {
            throw new Exception("You try to load an unknown attibute: " . $attrCode);
        }
    
        return static::$attributes[$attrCode];
    }    
}
