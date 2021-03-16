<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Akeneo\Objects\Product;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryTranslation;
use Splash\Models\Helpers\InlineHelper;

/**
 * Product Categories Fields Access
 */
trait CategoriesTrait
{
    //====================================================================//
    // PRODUCT CATEGORIES INFOS
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    public function buildCategorieFields()
    {
        //====================================================================//
        // Setup Field Factory
        $this->fieldsFactory()->setDefaultLanguage($this->locales->getDefault());

        //====================================================================//
        // Product Categorie Codes
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("categories")
            ->Name("Categorie Codes")
            ->MicroData("http://schema.org/Product", "publicCategory")
            ->isReadOnly();

        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Product Categorie Labels
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("categories_names")
                ->Name("Categorie Label")
                ->MicroData("http://schema.org/Product", "publicCategoryNames")
                ->setMultilang($isoLang)
                ->isReadOnly();
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
    public function getCategorieFields(string $key, string $fieldName)
    {
        switch ($fieldName) {
            //====================================================================//
            // Variant Readings
            case 'categories':
                //====================================================================//
                // Collect List of Categories Codes
                $categoriesCodes = array();
                /** @var Category $categorie */
                foreach ($this->object->getCategories() as $categorie) {
                    $categoriesCodes[] = $categorie->getCode();
                }
                $this->out[$fieldName] = InlineHelper::fromArray($categoriesCodes);

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
    public function getCategorieMultilangFields(string $key, string $fieldName)
    {
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Decode Multilang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            //====================================================================//
            // READ Fields
            if ('categories_names' != $baseFieldName) {
                continue;
            }
            //====================================================================//
            // Collect List of Categories Labels
            $categoriesNames = array();
            /** @var Category $categorie */
            foreach ($this->object->getCategories() as $categorie) {
                /** @var CategoryTranslation $translation */
                $translation = $categorie->getTranslation($isoLang);
                $categoriesNames[] = $translation->getLabel();
            }

            $this->out[$fieldName] = InlineHelper::fromArray($categoriesNames);
            unset($this->in[$key]);
        }
    }
}
