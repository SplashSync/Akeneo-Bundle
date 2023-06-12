<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Akeneo\Objects\Product;

use Splash\Client\Splash;

/**
 * Access to Product Labels
 */
trait LabelTrait
{
    //====================================================================//
    // PRODUCT CORE INFOS
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    public function buildMultilangFields()
    {
        //====================================================================//
        // Setup Splash Akeneo Connector
        $this->configuration->setup($this);
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
//                ->isReadOnly()
                ->isNotTested()
            ;

            //====================================================================//
            // Name with Options
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("label_with_options")
                ->Name("Product Name")
                ->MicroData("http://schema.org/Product", "name")
                ->setMultilang($isoLang)
                ->isReadOnly()
            ;

            //====================================================================//
            // Default Short Description
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("default_desc")
                ->Name("DEFAULT Description")
                ->MicroData("http://schema.org/Product", "description")
                ->setMultilang($isoLang)
                ->isReadOnly()
            ;
        }
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    public function getMultiLangFields(string $key, string $fieldName): void
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
                // Multi-lang Readings
                case 'label':
                    //====================================================================//
                    // Travis Mode => Force using Variant Name as Label
                    if (Splash::isTravisMode() && empty($this->attr->getLabelAttributeCode($this->object))) {
                        $this->out[$fieldName] = $this->object->getValue("variation_name", $isoLang)?->getData();
                        unset($this->in[$key]);

                        break;
                    }
                    $this->out[$fieldName] = $this->object->getLabel($isoLang);
                    unset($this->in[$key]);

                    break;
                case 'label_with_options':
                case 'default_desc':
                    $this->out[$fieldName] = $this->object->getLabel($isoLang);
                    unset($this->in[$key]);

                    break;
            }
        }
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    public function setMultiLangFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Decode MultiLang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            //====================================================================//
            // WRITE Fields
            if ('label' != $baseFieldName) {
                continue;
            }
            $labelCode = $this->attr->getLabelAttributeCode($this->object);
            //====================================================================//
            // Travis Mode => Force using Variant Name as Label
            if (Splash::isTravisMode() && empty($labelCode)) {
                $labelCode = "variation_name";
            }
            //====================================================================//
            // Write Data from Attributes Service
            if ($labelCode) {
                $this->attr->set(
                    $this->object,
                    str_replace("label", $labelCode, $fieldName),
                    $fieldData
                );
            }
            unset($this->in[$fieldName]);
        }
    }
}
