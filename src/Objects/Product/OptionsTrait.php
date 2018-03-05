<?php

namespace Splash\Akeneo\Objects\Product;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

use Pim\Component\Catalog\AttributeTypes;

use Splash\Core\SplashCore as Splash;

trait OptionsTrait {
 
    /**
     *  @abstract    Read Attribute Possibles Choices 
     * 
     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
     * 
     *  @return mixed
     */    
    protected function exportChoices( AttributeInterface $Attribute , $Language )
    {
        if ( $Attribute->getType() != AttributeTypes::OPTION_SIMPLE_SELECT ) { 
            return Null;
        }
        $Choices = array();
        foreach ($Attribute->getOptions() as $Option) {
            $Code   =  (string) $Option->getCode();  
            if ($Option->getOptionValues()->containsKey($Language) ) {
                $Choices[ $Code ]     =   $Option->getOptionValues()->get($Language)->getValue();
            } else {
                $Choices[ $Code ]     =   $Option->getTranslation($Language)->getLabel();
            }
        }
        return $Choices;
    } 
    
    /**
     *  @abstract    Read Attribute Data 
     * 
     *  @param  ProductInterface    $Object         Akeneo Product Object
     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
     * 
     *  @return mixed
     */    
    protected function exportOption(ProductInterface $Object, AttributeInterface $Attribute )
    {
        //====================================================================//
        // Check if Attribute is Used for this Object
        if( !in_array($Attribute->getCode() , $Object->getUsedAttributeCodes() ) ) {           
            return Null;
        }         
        $Value  =   $this->getAttributeValue($Object, $Attribute);
        if (is_object($Value)) {
            return (string) $Value->getCode();
        }
        return  (string) substr( (string) $Value , 1 , strlen( (string) $Value) - 2 );
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
    public function importOption(ProductInterface $Object, AttributeInterface $Attribute, $Data)
    {  
        if ( empty($Data) ) {
            return;
        } 
        return $this->setAttributeValue($Object, $Attribute, $Data);
    }
        
}
