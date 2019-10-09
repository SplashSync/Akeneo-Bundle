<?php

namespace Splash\Akeneo\Objects\Product;

use Splash\Core\SplashCore as Splash;

use Pim\Component\Catalog\Model\Product;
use Doctrine\ORM\EntityNotFoundException;

trait CrudTrait {
    
    //====================================================================//
    // PRODUCT LOADING
    //====================================================================//    
    
    /**
     * {@inheritdoc}
     */
    
    /**
     * Load Product
     * 
     * @param type $objectId
     * @return object|false
     */
    public function load($objectId) {  
        
        //====================================================================//
        // Load Product from Repository
        $product = $this->repository->find($objectId);
        if(!($product instanceof Product)) {
            return Splash::Log()->errTrace("Unable to find Akeneo Product " . $objectId);
        }
        
//        Splash::Log()->www("data", $product->getRawValues());
        return $product;
    }    
    
    //====================================================================//
    // PRODUCT CREATE
    //====================================================================//    
    
    /**
     * {@inheritdoc}
     */
    public function create($Manager, $Target) {            
        //====================================================================//
        // Saftey Check
        if ( !$Target || !class_exists($Target) ) { 
            return False; 
        }
//        try {
//            //====================================================================//
//            // Create a New PIM Product
//            $Object =   $this->Builder->createProduct();  
//        } catch ( \Exception $e) {
//            Splash::Log()->Err("Akeneo Product Create Failled");
//            Splash::Log()->Err($e->getMessage());    
//        }
        //====================================================================//
        // Return a New Object
        return  $Object;
    }
    
    //====================================================================//
    // PRODUCT UPDATE
    //====================================================================//
    
    /**
     * {@inheritdoc}
     */
    public function update($needed) {            
        //====================================================================//
        // Forward to Crud Service
//        if($needed && !$this->crud->update($this->object)) {
        if(!$this->crud->update($this->object)) {
            return false;
        }
        
        //====================================================================//
        // Return Object Id
        return  $this->getObjectIdentifier();
    } 
    
    //====================================================================//
    // PRODUCT CREATE
    //====================================================================//    
    
    /**
     * {@inheritdoc}
     */
    public function delete($ibjectId = null) {
        //====================================================================//
        // Saftey Check
        if ( !$ibjectId ) { 
            return False; 
        }
        try {
            $this->Remover->remove( $this->Attach($ibjectId) );         
            
            Splash::Log()->Msg("Akeneo Product Delete Done");
            Splash::Log()->Msg("Deleted => " . $ibjectId->getId());
            
        } catch ( EntityNotFoundException $e) {
            return True;
        } catch ( \Exception $e) {
            Splash::Log()->Err("Akeneo Product Delete Failed");
            return Splash::Log()->Err($e->getMessage());    
        }        
        return True;
    }   
    
//    /**
//     *  @abstract       Ensure Product and Attributes are Correctly Attached to Entity Manager
//     */    
//    private function Attach($Object) {
//        //====================================================================//
//        // Re-Attach Detached Product Unique Data 
//        $UniqueDatas    =   $Object->getUniqueData();
//        foreach ( $UniqueDatas as $Index => &$UniqueData ) {
//            if ( !$this->EntityManager->contains($UniqueData) ) {
//                $UniqueDatas[$Index] = $this->EntityManager->merge($UniqueData);
//            }
//        }
////        $Object->setUniqueData($UniqueDatas);
//        //====================================================================//
//        // Re-Attach Detached Product itSelf 
//        if ( !$this->EntityManager->contains($Object) ) {
//            return $this->EntityManager->merge($Object);
//        }
//        return $Object;
//    }
    
    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        if (empty($this->object)) {
            return false;
        }
        return (string) $this->object->getId();
    }    
}
