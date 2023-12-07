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

namespace Splash\Akeneo\Objects\Category;

use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslationInterface;
use Splash\Core\SplashCore as Splash;

/**
 * Category Core Fields Access
 */
trait CoreTrait
{
    //====================================================================//
    // PRODUCT CORE INFOS
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    public function buildCoreFields(): void
    {
        //====================================================================//
        // Setup Splash Akeneo Connector
        $this->configuration->setup($this);
        //====================================================================//
        // Category Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("code")
            ->name('Code')
            ->isRequired()
            ->isListed()
            ->isNotTested()
        ;
        //====================================================================//
        // Category Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("label")
            ->name('Label')
            ->isReadOnly()
            ->isListed()
        ;
        //====================================================================//
        // Setup Field Factory
        $this->fieldsFactory()->setDefaultLanguage($this->locales->getDefault());
        //====================================================================//
        // Walk on All Available Attributes
        foreach ($this->locales->getAll() as $isoCode) {
            //====================================================================//
            // Category Name
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("name")
                ->name('Name')
                ->microData("http://schema.org/ProductCollection", "name")
                ->setMultiLang($isoCode)
                ->isRequired($this->locales->isDefault($isoCode))
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
    public function getCoreFields(string $key, string $fieldName): void
    {
        switch ($fieldName) {
            //====================================================================//
            // Variant Readings
            case 'code':
            case 'label':
                $this->getGeneric($fieldName);

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    public function getCoreTranslatedFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Decode Multi-lang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            if ('name' != $baseFieldName) {
                continue;
            }
            /** @var CategoryTranslationInterface $translation */
            $translation = $this->object->getTranslation($isoLang);
            $this->out[$fieldName] = $translation->getLabel();
            unset($this->in[$key]);
        }
    }

    /**
     * Write Given Fields
     *
     * @param string      $fieldName Field Identifier / Name
     * @param null|string $fieldData Field Data
     *
     * @return void
     */
    public function setCoreTranslatedFields(string $fieldName, ?string $fieldData): void
    {
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Decode Multi-lang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            if ('name' != $baseFieldName) {
                continue;
            }
            //====================================================================//
            // Update Field Value
            /** @var CategoryTranslationInterface $translation */
            $translation = $this->object->getTranslation($isoLang);
            $current = $translation->getLabel();
            if ($fieldData && ($current != $fieldData)) {
                $translation->setLabel($fieldData);
                $this->needUpdate();
            }
            unset($this->in[$fieldName]);
        }
    }

    /**
     * Write Given Fields
     *
     * @param string    $fieldName Field Identifier / Name
     * @param null|bool $fieldData Field Data
     *
     * @return void
     */
    protected function setCoreBoolFields(string $fieldName, ?bool $fieldData): void
    {
        switch ($fieldName) {
            case 'code':
                if ($fieldData != $this->object->getCode()) {
                    Splash::log()->errNull(
                        "You can't update Category Code, please delete and recreate the category."
                    );
                }

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
