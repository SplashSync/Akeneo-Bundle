<?php

namespace Splash\Akeneo\Objects\Product\Attributes;

use Splash\Core\SplashCore as Splash;

use Pim\Component\Catalog\Model\AttributeInterface as Attribute;
//use Pim\Component\Catalog\Model\ProductInterface as Product;
//use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\EntityWithValuesInterface as Product;

/**
 * Import / Export of Product Attribute Values
 */
trait CoreTrait {

    /**
     * CORE - Read Attribute Data with Local & Scope Detection
     *
     * @param  Product    $product         Akeneo Product Object
     * @param  Attribute  $attribute      Akeneo Attribute Object
     * @param string $isoLang
     * @param string $channel
     *
     * @return mixed
     */
    protected function getCoreValue(Product $product, Attribute $attribute, string $isoLang, string $channel)
    {
        //====================================================================//
        // Get Attribute Code
        $code = $attribute->getCode();
        //====================================================================//
        // Check if Attribute is Used for this Product
        if( !in_array($code , $product->getUsedAttributeCodes() ) ) {
            //====================================================================//
            // Load Value from Parent Product
            $parent = $product->getParent();
            if($parent instanceof Product) {
                return $this->getCoreValue($parent, $attribute, $isoLang, $channel);
            }
            
            return Null;
        }
        //====================================================================//
        // Load Product Value Object
        $value = $product->getValue(
            $code, 
            $attribute->isLocalizable() ? $isoLang : null, 
            $attribute->isScopable() ? $channel : null
        );
        if(null == $value) {
            return Null;
        }
        
        //====================================================================//
        // Return Raw Product Value Data
        return $value->getData();   
    }
    
}
