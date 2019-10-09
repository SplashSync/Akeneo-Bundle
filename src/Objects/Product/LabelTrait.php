<?php

namespace Splash\Akeneo\Objects\Product;

/**
 * Access to Product Labels
 */
trait LabelTrait {
    
    //====================================================================//
    // PRODUCT CORE INFOS
    //====================================================================//
    
    /**
     * Build Fields using FieldFactory
     */
    public function buildMultilangFields()
    {
        //====================================================================//
        // Setup Field Factory
        $this->fieldsFactory()->setDefaultLanguage($this->locales->getDefault());
        
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            
            //====================================================================//
            // Name without Options
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("label")
                ->Name("Label")
                ->MicroData("http://schema.org/Product", "alternateName")
                ->setMultilang($isoLang)
                ->isListed($this->locales->isDefault($isoLang))
                ->isReadOnly()
            ;
            
        }
    }   
    
    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    public function getMultilangFields(string $key, string $fieldName)
    {
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {        
            //====================================================================//
            // Decode Multilang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            //====================================================================//
            // READ Fields
            switch ($baseFieldName) {
                //====================================================================//
                // Multilang Readings
                case 'label':
                    $this->out[$fieldName] = $this->object->getLabel($isoLang);
                    unset($this->in[$key]);
                    break;
            }
        }
    }   
}
