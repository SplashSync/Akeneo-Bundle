<?php

namespace Splash\Akeneo\Objects\Product\Variants;

use Splash\Core\SplashCore as Splash;

use Splash\Bundle\Annotation as SPL;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

trait CoreTrait {
    
    //====================================================================//
    // PRODUCT VARIANT CORE INFOS
    //====================================================================//
    
    /**
     * Build Fields using FieldFactory
     */
    public function buildVariantCoreFields()
    {
        //====================================================================//
        // Product SKU
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("isVariant")
            ->Name("Is Variant")
            ->group("Metedata")
            ->isListed()
            ->isReadOnly();
    }   
    
    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    public function getVariantCoreFields(string $key, string $fieldName)
    {
        switch ($fieldName) {
            //====================================================================//
            // Variant Readings
            case 'isVariant':
                $this->getGenericBool(variant);
                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }      
    
    

    
    
}
