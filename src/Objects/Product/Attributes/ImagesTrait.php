<?php

namespace Splash\Akeneo\Objects\Product;

use Splash\Models\Objects\ImagesTrait as SplashImages;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


trait ImagesTrait {
    
    use SplashImages;
    
    protected function exportImage(ProductInterface $object, AttributeInterface $attribute )
    {
        //====================================================================//
        // Check if Attribute is Used for this Object
        if( !in_array($attribute->getCode() , $object->getUsedAttributeCodes() ) ) {           
            return Null;
        }         
        
        $image   =   $this->getAttributeValue($object, $attribute);
        return self::Images()->Encode(
                    $image->getOriginalFilename(),
                    $image->getKey(),
                    $this->Catalog_Storage_Dir . "/",
                    $this->Router->generate(
                            "pim_enrich_media_show", 
                            [ "filename" => urlencode($image->getKey()), "filter" => "preview" ],
                             UrlGeneratorInterface::ABSOLUTE_URL)
                );        
    } 
    
}
