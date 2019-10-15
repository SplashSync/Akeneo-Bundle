<?php

namespace Splash\Akeneo\Objects\Product;

use Splash\Core\SplashCore as Splash;

use Pim\Component\Catalog\Model\Product;


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
        
        return $product;
    }    
    
    //====================================================================//
    // PRODUCT CREATE
    //====================================================================//    
    
    /**
     * {@inheritdoc}
     */
    public function create() 
    {
        //====================================================================//
        // Create a New PIM Product
        $product =   $this->crud->createProduct($this->in);  
        if(null === $product) {
            return Splash::Log()->errTrace("Akeneo Product Create Failled");
        }        
        //====================================================================//
        // Return a New Object
        return  $product;
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
    public function delete($objectId = null) 
    {
        //====================================================================//
        // Try Loading the Product
        $product = $this->load($objectId);
        if (!$product) {
            return true;
        }
        
        
        //====================================================================//
        // Forward to Crud Service
        $this->crud->delete($product);
        
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
