<?php

namespace Splash\Akeneo\Objects\Product;

use Splash\Core\SplashCore as Splash;

use Splash\Bundle\Annotation as SPL;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

trait CRUDTrait {
    
    //====================================================================//
    // PRODUCT UPDATE
    //====================================================================//
    
    /**
     *  @abstract       Update Object Data in Database
     * 
     *  @param  mixed   $Manager        Local Object Entity/Document Manager
     *  @param  string  $Object         Local Object
     * 
     *  @return         mixed
     */
    public function update($Manager, $Object) {            
        //====================================================================//
        // Saftey Check
        if ( !$Object ) { 
            return False; 
        }
        try {
            //====================================================================//
            // Validate Changes        
            $this->Validator->validate($Object);         
            //====================================================================//
            // Save Changes        
            $this->Saver->save($Object);         
        } catch ( \Exception $e) {
            Splash::Log()->Err($e->getMessage());    
        }
        //====================================================================//
        // Return Object Id
        return  $Object->getId();
    } 
    
}
