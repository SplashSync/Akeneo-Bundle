<?php

namespace Splash\Akeneo\Objects\Product;

use Splash\Core\SplashCore as Splash;

use Splash\Bundle\Annotation as SPL;

use Doctrine\ORM\EntityNotFoundException;

trait CRUDTrait {
    
    //====================================================================//
    // PRODUCT CREATE
    //====================================================================//    
    
    /**
     *  @abstract       Create a New Object
     * 
     *  @param  mixed   $Manager        Local Object Entity/Document Manager
     *  @param  string  $Target         Local Object Class Name
     * 
     *  @return         mixed
     */
    public function create($Manager, $Target) {            
        //====================================================================//
        // Saftey Check
        if ( !$Target || !class_exists($Target) ) { 
            return False; 
        }
        try {
            //====================================================================//
            // Create a New PIM Product
            $Object =   $this->Builder->createProduct();  
        } catch ( \Exception $e) {
            Splash::Log()->Err("Akeneo Product Create Failled");
            Splash::Log()->Err($e->getMessage());    
        }
        //====================================================================//
        // Return a New Object
        return  $Object;
    }
    
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
//dump($Object);
        try {
            //====================================================================//
            // If Product is New => Disable Doctrine postUpdate Event
            // This is done to prevent Repeated Commits (Create + Update)
            if ( !$Object->getId() ) {
                Splash::Local()->setListnerState("postUpdate", False);
//            } else {
//                Splash::Local()->setListnerState("postPersist", False);
            }
            
            
//            //====================================================================//
//            // Re-Attach Product Unique Data 
//            $UniqueDatas    =   $Object->getUniqueData();
//            foreach ( $UniqueDatas as $Index => $UniqueData ) {
//                $UniqueDatas[$Index] = $this->EntityManager->merge($UniqueData);
//            }
//            $Object->setUniqueData($UniqueDatas);
            //====================================================================//
            // Delete Product
//            $this->Remover->remove($this->EntityManager->merge($Object)); 
            
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//Splash::Log()->www("Updated => " , $Object->getId());
            //====================================================================//
            // Validate Changes        
            $this->Validator->validate($Object);         
            //====================================================================//
            // Save Changes        
            $this->Saver->save($Object);        
            
            Splash::Log()->Err("Akeneo Product Update Done");
            
        } catch ( \Exception $e) {
            Splash::Log()->Err("Akeneo Product Update Failled");
            Splash::Log()->Err($e->getMessage());    
//dump($e);
        }
//        $this->EntityManager->clear();
        //====================================================================//
        // Whatever => Enable Doctrine postUpdate Event Again!
        Splash::Local()->setListnerState("postPersist", True);
        Splash::Local()->setListnerState("postUpdate", True);
        //====================================================================//
        // Return Object Id
        return  $Object->getId();
    } 
    
    //====================================================================//
    // PRODUCT CREATE
    //====================================================================//    
    
    /**
     *  @abstract       Delete a Product
     * 
     *  @param  mixed   $Manager        Local Object Entity/Document Manager
     *  @param  string  $Object         Local Object
     * 
     *  @return         mixed
     */
    public function delete($Manager, $Object) {
        //====================================================================//
        // Saftey Check
        if ( !$Object ) { 
            return False; 
        }
        try {
            //====================================================================//
            // Re-Attach Product Unique Data 
            $UniqueDatas    =   $Object->getUniqueData();
            foreach ( $UniqueDatas as $Index => $UniqueData ) {
                $UniqueDatas[$Index] = $this->EntityManager->merge($UniqueData);
            }
            $Object->setUniqueData($UniqueDatas);
            //====================================================================//
            // Delete Product
            $this->Remover->remove($this->EntityManager->merge($Object));         

//        $this->EntityManager->clear("Pim\Component\Catalog\Model\Product");   
        $this->EntityManager->getRepository("PimCatalogBundle:Product")->clear();
        $Manager->clear();         
dump("CLEAR");
exit;
//            $this->Remover->remove($Object);         
            
//\Splash\Tests\Tools\TestCase::rebootKernel();            
            
        } catch ( EntityNotFoundException $e) {
            return True;
        } catch ( \Exception $e) {
            Splash::Log()->Err("Akeneo Product Delete Failled");
            return Splash::Log()->Err($e->getMessage());    
        }        
        return True;
    }    
    
}
