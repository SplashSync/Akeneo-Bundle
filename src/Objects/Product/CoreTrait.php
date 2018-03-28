<?php

namespace Splash\Akeneo\Objects\Product;

use Splash\Core\SplashCore as Splash;

use Splash\Bundle\Annotation as SPL;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

trait CoreTrait
{
    
    //====================================================================//
    // PRODUCT CORE INFOS
    //====================================================================//
    
    /**
     * @SPL\Field(
     *          id      =   "identifier",
     *          type    =   "varchar",
     *          name    =   "Reference",
     *          inlist  =   true,
     *          write   =   false,
     * )
     */
    protected $identifier;
    
    /**
     * @SPL\Field(
     *          id      =   "enabled",
     *          type    =   "bool",
     *          name    =   "Active",
     *          itemtype=   "http://schema.org/Product", itemprop="offered",
     * )
     */
    protected $enabled;
    
    public function getEnabled($Object)
    {
        return $Object->isEnabled();
    }
    
    
    /**
     *  @abstract    Read Attribute Data with Lacal & Scope Detection
     *
     *  @param  ProductInterface    $Object         Akeneo Product Object
     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
     *
     *  @return mixed
     */
    public function getAttributeValue(ProductInterface $Object, AttributeInterface $Attribute)
    {
        //====================================================================//
        // Check if Attribute is Used for this Object
        if (!in_array($Attribute->getCode(), $Object->getUsedAttributeCodes())) {
            return null;
        }
        
        //====================================================================//
        // Value is Monolanguage
        if (!$Attribute->isLocalizable()) {
            //====================================================================//
            // Value is Similar for All Langs & All Channels
            if (!$Attribute->isScopable()) {
                return $Object->getValue($Attribute->getCode())->getData();
            //====================================================================//
            // Value is Channels Specific
            } else {
                return $Object->getValue($Attribute->getCode(), null, $this->Config["scope"])->getData();
            }
        }
        
        //====================================================================//
        // Value is Multilanguage Specific
        $Raw    =   $Object->getRawValues();
        $Value  =   null;
        if (!$Attribute->isScopable() && $Attribute->isLocalizable()) {
            $Value =    $Raw[$Attribute->getCode()]["<all_channels>"];
        }
        if (isset($Raw[$Attribute->getCode()][$this->Config["scope"]])) {
            $Value =    $Raw[$Attribute->getCode()][$this->Config["scope"]];
        }
        return  is_array($Value) ? $Value : null;
    }
    
    /**
     *  @abstract    Write Attribute Data with Local & Scope Detection
     *
     *  @param  ProductInterface    $Object         Akeneo Product Object
     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
     *  @param  mixed               $Data           Field Input Splash Formated Data
     *
     *  @return bool
     */
    public function setAttributeValue(ProductInterface $Object, AttributeInterface $Attribute, $Data)
    {
        $FieldsValues = array();
        
        //====================================================================//
        // Value is Similar for All Langs & All Channels
        if (!$Attribute->isScopable() && !$Attribute->isLocalizable()) {
            $FieldsValues[$Attribute->getCode()] = [["locale" => null, "scope" => null, "data" => $Data]];
        }
        
        //====================================================================//
        // Value is Channels Specific
        if ($Attribute->isScopable() && !$Attribute->isLocalizable()) {
            $FieldsValues[$Attribute->getCode()] = [["locale" => null, "scope" => $this->Config["scope"], "data" => $Data]];
        }
        
        //====================================================================//
        // Value is Multilanguage Specific
        if ($Attribute->isLocalizable()) {
            $Scope  =   $Attribute->isScopable() ? $this->Config["scope"] : null;
            $LocalizedValues = [];
            foreach ($Data as $Locale => $Value) {
                $LocalizedValues[]  =   ["locale" => $Locale, "scope" => $Scope, "data" => $Value];
            }
            $FieldsValues[$Attribute->getCode()] = $LocalizedValues;
        }
        
        //====================================================================//
        // Update Attribute with Error detection
        try {
            return $this->Updater->update($Object, [ "values" => $FieldsValues ]);
        } catch (\Exception $e) {
            Splash::Log()->Err($e->getMessage());
        }
    }
}
