<?php

namespace Splash\Akeneo\Objects\Core;

use Splash\Bundle\Annotation as SPL;

/**
 * Acces to Objects Generic Metadata
 */
trait ObjectMetadataTrait {
    
    /**
     * Build Fields using FieldFactory
     */
    public function buildDatesFields()
    {
        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->Identifier("created")
            ->Name("Creation Date")
            ->MicroData("http://schema.org/DataFeedItem", "dateCreated")
            ->group("Metedata")
            ->isReadOnly();
        
        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->Identifier("updated")
            ->Name("Updated Date")
            ->MicroData("http://schema.org/DataFeedItem", "dateModified")
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
    public function getDatesFields(string $key, string $fieldName)
    {
        switch ($fieldName) {
            //====================================================================//
            // Variant Readings
            case 'created':
            case 'updated':
                $this->getGenericDateTime($fieldName);
                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }        
}
