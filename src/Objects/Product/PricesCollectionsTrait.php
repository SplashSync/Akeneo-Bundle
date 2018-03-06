<?php

namespace Splash\Akeneo\Objects\Product;

use Splash\Models\Objects\PricesTrait;

use Symfony\Component\Intl\Intl;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

trait PricesCollectionsTrait {
    
    use PricesTrait;
    
    /**
     *  @abstract    Write Attribute Data 
     * 
     *  @param  ProductInterface    $Object         Akeneo Product Object
     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
     *  @param  mixed               $Data           Field Input Splash Formated Data
     * 
     *  @return bool
     */    
    protected function importPrice(ProductInterface $Object, AttributeInterface $Attribute, $Data)
    {
        return $this->setAttributeValue($Object, $Attribute, [[ "amount" => self::Prices()->TaxExcluded($Data), "currency" => $Data["code"] ]]);
    } 
    
    /**
     *  @abstract    Write Attribute Data with Local & Scope Detection 
     * 
     *  @param  ProductInterface    $Object         Akeneo Product Object
     *  @param  AttributeInterface  $Attribute      Akeneo Attribute Object
     *  @param  mixed               $Data           Field Input Splash Formated Data
     * 
     *  @return         mixed           Splash Formated Data
     */    
    protected function exportPrice(ProductInterface $Object, AttributeInterface $Attribute )
    {
        //====================================================================//
        // Check if Attribute is Used for this Object
        if( !in_array($Attribute->getCode() , $Object->getUsedAttributeCodes() ) ) {           
            return $this->buildPrice(0);
        } 
        //====================================================================//
        // Search for Currency Price in Collection
        foreach ($this->getAttributeValue($Object, $Attribute) as $ProductPrice) 
        {
            if ( $ProductPrice->getCurrency() == $this->Config["currency"]) {
                return $this->buildPrice($ProductPrice->getData());
            } 
        }
        return $this->buildPrice(0);
    } 
    
    private function buildPrice( $HtPrice ) {
        return self::Prices()->Encode( 
                    (double) $HtPrice, (double) 0, Null, 
                    $this->Config["currency"], 
                    Intl::getCurrencyBundle()->getCurrencySymbol($this->Config["currency"]),
                    Intl::getCurrencyBundle()->getCurrencyName($this->Config["currency"])
                );
    }
}
