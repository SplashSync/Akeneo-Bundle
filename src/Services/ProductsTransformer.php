<?php

/*
 * This file is part of SplashSync Project.
 *
 * Copyright (C) Splash Sync <www.splashsync.com>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @abstract    Akeneo Bundle Data Transformer for Splash Bundle
 * @author      B. Paquier <contact@splashsync.com>
 */

namespace   Splash\Akeneo\Services;

use Splash\Components\FieldsFactory;
use Splash\Local\Objects\Transformer;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\RouterInterface;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository;
use Pim\Component\Catalog\Builder\ProductBuilder;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;

use Splash\Bundle\Annotation as SPL;
use Splash\Bundle\Annotation\Field;


use Pim\Component\Catalog\AttributeTypes;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

class ProductsTransformer extends Transformer
{
    use \Splash\Akeneo\Objects\Product\CRUDTrait;
    use \Splash\Akeneo\Objects\Product\CoreTrait;
    use \Splash\Akeneo\Objects\Product\DatesTrait;
    use \Splash\Akeneo\Objects\Product\OptionsTrait;
    use \Splash\Akeneo\Objects\Product\ImagesTrait;
    use \Splash\Akeneo\Objects\Product\PricesCollectionsTrait;
        
    /**
     * @var EntityManagerInterface
     */
    private $EntityManager;
    
    /**
     * @var RouterInterface
     */
    private $Router;
    
    /**
     * @var ProductBuilder
     */
    private $Builder;
    
    /**
     * @var ObjectUpdaterInterface
     */
    private $Updater;
    
    /**
     * @var RecursiveValidator
     */
    private $Validator;
    
    /**
     * @var SaverInterface
     */
    private $Saver;

    /**
     * @var RemoverInterface
     */
    private $Remover;
        
    /**
     * @var array
     */
    private $Config;
    
    /**
     * @var string
     */
    private $Catalog_Storage_Dir;
    
    public function __construct(
            
            EntityManagerInterface      $EntityManager,
            RouterInterface             $Router,
            
            ProductBuilder              $Builder,
            ObjectUpdaterInterface      $Updater,
            RecursiveValidator          $Validator,
            SaverInterface              $Saver,
            RemoverInterface            $Remover,
            
            array                       $Config,
            string                      $Catalog_Storage_Dir
        ) {
        $this->EntityManager        =   $EntityManager;
        $this->Router               =   $Router;
        
        $this->Builder              =   $Builder;
        $this->Updater              =   $Updater;
        $this->Validator            =   $Validator;
        $this->Saver                =   $Saver;
        $this->Remover              =   $Remover;
        
        
        $this->Config               =   $Config;
        $this->Catalog_Storage_Dir  =   $Catalog_Storage_Dir;
        
        return;
    }
    
    //====================================================================//
    // PRODUCT FIELDS ACCESS
    //====================================================================//

    /**
     *  @abstract       Export Field Data from Local Object
     *
     *  @param  mixed   $Object         Current Local Object
     *  @param  string  $Id             Splash Field Id
     *  @param  string  $Type           Splash Field Type
     *
     *  @return         mixed           Splash Formated Data
     */
    public function exportCore($Object, string $Id, string $Type)
    {
        //====================================================================//
        // Load All Available Attributes
        $Attribute     =   $this->EntityManager
                ->getRepository("PimCatalogBundle:Attribute")
                ->findOneByIdentifier($Id);
        
        if (empty($Attribute)) {
            return parent::exportCore($Object, $Id, $Type);
        }

        return $this->getAttributeData($Object, $Attribute);
    }

    /**
     *  @abstract       Import Field Data to Local Object
     *
     *  @param  mixed   $Object         Current Local Object
     *  @param  string  $Id             Splash Field Id
     *  @param  string  $Type           Splash Field Type
     *  @param  mixed   $Data           Field Input Splash Formated Data
     *
     *  @return         mixed           Splash Formated Data
     */
    public function importCore($Object, string $Id, string $Type, $Data)
    {
        //====================================================================//
        // Load All Available Attributes
        $Attribute     =   $this->EntityManager
                ->getRepository("PimCatalogBundle:Attribute")
                ->findOneByIdentifier($Id);
        
        if (empty($Attribute)) {
            return parent::importCore($Object, $Id, $Type, $Data);
        }

        return $this->setAttributeData($Object, $Attribute, $Data);
    }
    
    //====================================================================//
    // PRODUCT ATTRIBUTES FIELDS DEFINITION
    //====================================================================//

    /**
     *  @abstract       Populate Product Object Fields Definition
     *
     *  @param  string      $ObjectType          Splash Object Type Name
     *
     *  @return         array           Splash Formated Fields Definitions
     */
    public function Fields($ObjectType)
    {
        if ($ObjectType != "Product") {
            return array();
        }
        
        //====================================================================//
        // Load All Available Attributes
        $Attributes     =   $this->EntityManager
                ->getRepository("PimCatalogBundle:Attribute")
                ->findAll();

        //====================================================================//
        // Create Fields List Array
        $List    =   array();
        
        foreach ($Attributes as $Attribute) {

            //====================================================================//
            // Detect Field Type
            $FieldType  =   $this->getSplashAttributeType($Attribute);
            if (!$FieldType) {
                continue;
            }

            //====================================================================//
            // Field Core Infos
            $Field  =   new Field();
            //====================================================================//
            // Field Core Infos
            $Field->id      =   $Attribute->getCode();
            $Field->type    =   $FieldType;
            $Field->name    =   $Attribute->getTranslation($this->Config["language"])->getLabel();
            $Field->desc    =   $Attribute->getTranslation($this->Config["language"])->getLabel();
            $Field->group   =   $Attribute->getGroup()->getTranslation($this->Config["language"])->getLabel();
            
            //====================================================================//
            // is Field Required ?
            if ($Attribute->isRequired()) {
                $Field->required = true;
            }
            
            //====================================================================//
            // is Field read Only ?
            if (in_array($Attribute->getType(), [ AttributeTypes::FILE , AttributeTypes::IMAGE ])) {
                $Field->write = false;
            }

            //====================================================================//
            // Does the Field Have MetaData ?
            $MetaData   =   $this->getSplashAttributeMetaData($Attribute);
            if ($MetaData) {
                $Field->itemprop    =   $MetaData["itemprop"];
                $Field->itemtype    =   $MetaData["itemtype"];
            }
            
            //====================================================================//
            // Does the Field Have Choices Values ?
            if ($Attribute->getType() == AttributeTypes::OPTION_SIMPLE_SELECT) {
                $Field->choices     =   $this->exportChoices($Attribute, $this->Config["language"]);
            }
            
            //====================================================================//
            // Add Field To List
            $List[]     =   $Field;
        }
    

        //====================================================================//
        // Publish Fields
        return $List;
    }
    
    /**
     *  @abstract       Convert Akeneo Attribute Type to Splash Field Type
     *
     *  @param  AttributeInterface $Attribute       Akeneo Attribute Object
     *
     *  @return         string
     */
    private function getSplashAttributeType(AttributeInterface $Attribute)
    {
        switch ($Attribute->getType()) {
            case AttributeTypes::BOOLEAN:
                return SPL_T_BOOL;

            case AttributeTypes::DATE:
                return SPL_T_DATE;

//            case AttributeTypes::FILE:
//                return SPL_T_FILE;

            case AttributeTypes::IMAGE:
                return SPL_T_IMG;

            case AttributeTypes::PRICE_COLLECTION:
                return SPL_T_PRICE;

            case AttributeTypes::IDENTIFIER:
            case AttributeTypes::OPTION_SIMPLE_SELECT:
                return SPL_T_VARCHAR;

            case AttributeTypes::TEXT:
            case AttributeTypes::TEXTAREA:
                return $Attribute->isLocalizable() ? SPL_T_MVARCHAR : SPL_T_VARCHAR;

        }

        return null;
    }
    
    /**
     *  @abstract       Convert Akeneo Attribute Type to Splash Field Type
     *
     *  @param  AttributeInterface $Attribute       Akeneo Attribute Object
     *
     *  @return         string
     */
    private function getSplashAttributeMetaData(AttributeInterface $Attribute)
    {
        
        //====================================================================//
        // Check if Attribute Code is part of Known Codes
        if (isset($this->Config["products"][$Attribute->getCode()])) {
            return $this->Config["products"][$Attribute->getCode()];
        }

        //====================================================================//
        // Return default Custom Field Metadata
        return array(
            "itemtype"  =>  "http://meta.schema.org/additionalType",
            "itemprop"  =>  $Attribute->getCode()
        );
    }
    
    /**
     *  @abstract       Export Field Data from Local Object
     *
     *  @param  ProductInterface   $Object          Akeneo Product Object
     *  @param  AttributeInterface $Attribute       Akeneo Attribute Object
     *
     *  @return         mixed           Splash Formated Data
     */
    public function getAttributeData(ProductInterface $Object, AttributeInterface $Attribute)
    {
        //====================================================================//
        // Read & Convert Attribute Value
        switch ($Attribute->getType()) {
            case AttributeTypes::BOOLEAN:
                return $this->getAttributeValue($Object, $Attribute);

            case AttributeTypes::DATE:
                return $this->exportDates($Object, $Attribute);

            case AttributeTypes::FILE:
                return null;

            case AttributeTypes::IMAGE:
                return $this->exportImage($Object, $Attribute);

            case AttributeTypes::OPTION_SIMPLE_SELECT:
                return $this->exportOption($Object, $Attribute);

            case AttributeTypes::IDENTIFIER:
            case AttributeTypes::TEXT:
            case AttributeTypes::TEXTAREA:
                return $this->getAttributeValue($Object, $Attribute);

            case AttributeTypes::PRICE_COLLECTION:
                return $this->exportPrice($Object, $Attribute);

        }
        return null;
    }

    /**
     *  @abstract       Export Field Data from Local Object
     *
     *  @param  ProductInterface    $Object         Akeneo Product Object
     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
     *  @param  mixed               $Data           Field Input Splash Formated Data
     *
     *
     *  @return         bool
     */
    public function setAttributeData(ProductInterface $Object, AttributeInterface $Attribute, $Data)
    {
        //====================================================================//
        // Read & Convert Attribute Value
        switch ($Attribute->getType()) {
            case AttributeTypes::BOOLEAN:
                return $this->setAttributeValue($Object, $Attribute, $Data);

            case AttributeTypes::DATE:
                return $this->setAttributeValue($Object, $Attribute, $Data);

            case AttributeTypes::FILE:
                return null;

            case AttributeTypes::IMAGE:
                return $this->exportImage($Object, $Attribute, $Data);

            case AttributeTypes::OPTION_SIMPLE_SELECT:
                return $this->importOption($Object, $Attribute, $Data);

            case AttributeTypes::IDENTIFIER:
            case AttributeTypes::TEXT:
            case AttributeTypes::TEXTAREA:
                return $this->setAttributeValue($Object, $Attribute, $Data);

            case AttributeTypes::PRICE_COLLECTION:
                return $this->importPrice($Object, $Attribute, $Data);

        }

        return null;
    }
}
