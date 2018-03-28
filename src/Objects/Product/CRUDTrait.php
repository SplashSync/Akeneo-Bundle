<?php

namespace Splash\Akeneo\Objects\Product;

use Splash\Core\SplashCore as Splash;

use Splash\Bundle\Annotation as SPL;

use Doctrine\ORM\EntityNotFoundException;

trait CRUDTrait
{
    
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
    public function create($Manager, $Target)
    {
        //====================================================================//
        // Saftey Check
        if (!$Target || !class_exists($Target)) {
            return false;
        }
        try {
            //====================================================================//
            // Create a New PIM Product
            $Object =   $this->Builder->createProduct();
        } catch (\Exception $e) {
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
    public function update($Manager, $Object)
    {
        //====================================================================//
        // Saftey Check
        if (!$Object) {
            return false;
        }
        try {
            //====================================================================//
            // If Product is New => Disable Doctrine postUpdate Event
            // This is done to prevent Repeated Commits (Create + Update)
            if (!$Object->getId()) {
                Splash::Local()->setListnerState("postUpdate", false);
            }
            //====================================================================//
            // Re-Attach Product to Entity Manager
            $AttachedObject =   $this->Attach($Object);
            //====================================================================//
            // Validate Changes
            $this->Validator->validate($AttachedObject);
            //====================================================================//
            // Save Changes
            $this->Saver->save($AttachedObject);
            
            Splash::Log()->Msg("Akeneo Product Update Done");
            Splash::Log()->Msg("Updated => " . $AttachedObject->getId());
        } catch (\Exception $e) {
            Splash::Log()->Err("Akeneo Product Update Failed");
            return Splash::Log()->Err($e->getMessage());
        }
        //====================================================================//
        // Whatever => Enable Doctrine postUpdate Event Again!
        Splash::Local()->setListnerState("postPersist", true);
        Splash::Local()->setListnerState("postUpdate", true);
        //====================================================================//
        // Return Object Id
        return  $AttachedObject->getId();
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
    public function delete($Manager, $Object)
    {
        //====================================================================//
        // Saftey Check
        if (!$Object) {
            return false;
        }
        try {
            $this->Remover->remove($this->Attach($Object));
            
            Splash::Log()->Msg("Akeneo Product Delete Done");
            Splash::Log()->Msg("Deleted => " . $Object->getId());
        } catch (EntityNotFoundException $e) {
            return true;
        } catch (\Exception $e) {
            Splash::Log()->Err("Akeneo Product Delete Failed");
            return Splash::Log()->Err($e->getMessage());
        }
        return true;
    }
    
    /**
     *  @abstract       Ensure Product and Attributes are Correctly Attached to Entity Manager
     */
    private function Attach($Object)
    {
        //====================================================================//
        // Re-Attach Detached Product Unique Data
        $UniqueDatas    =   $Object->getUniqueData();
        foreach ($UniqueDatas as $Index => &$UniqueData) {
            if (!$this->EntityManager->contains($UniqueData)) {
                $UniqueDatas[$Index] = $this->EntityManager->merge($UniqueData);
            }
        }
//        $Object->setUniqueData($UniqueDatas);
        //====================================================================//
        // Re-Attach Detached Product itSelf
        if (!$this->EntityManager->contains($Object)) {
            return $this->EntityManager->merge($Object);
        }
        return $Object;
    }
}
