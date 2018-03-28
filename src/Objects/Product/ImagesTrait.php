<?php

namespace Splash\Akeneo\Objects\Product;

use Splash\Models\Objects\ImagesTrait as SplashImages;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait ImagesTrait
{
    use SplashImages;
    
    protected function exportImage(ProductInterface $Object, AttributeInterface $Attribute)
    {
        //====================================================================//
        // Check if Attribute is Used for this Object
        if (!in_array($Attribute->getCode(), $Object->getUsedAttributeCodes())) {
            return null;
        }
        
        $Image   =   $this->getAttributeValue($Object, $Attribute);
        return self::Images()->Encode(
                    $Image->getOriginalFilename(),
                    $Image->getKey(),
                    $this->Catalog_Storage_Dir . "/",
                    $this->Router->generate(
                            "pim_enrich_media_show",
                            [ "filename" => urlencode($Image->getKey()), "filter" => "preview" ],
                             UrlGeneratorInterface::ABSOLUTE_URL
                    )
                );
    }
}
