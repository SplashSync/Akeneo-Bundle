<?php

namespace Splash\Akeneo\Objects\Product;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\AttributeInterface;


trait OptionsTrait {
    
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
        return  $this->getAttributeValue($Object, $Attribute)->getCode();
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
