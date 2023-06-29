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

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslation;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslationInterface;
use Splash\Akeneo\Models\CategoriesUpdater;
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
    public function buildCategoriesFields(): void
    {
        //====================================================================//
        // Setup Field Factory
        $this->fieldsFactory()->setDefaultLanguage($this->locales->getDefault());
        //====================================================================//
        // Product Categories Codes
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("categories")
            ->name("Categories Codes")
            ->microData("http://schema.org/Product", "publicCategory")
            ->addChoices($this->getCategoriesChoices())
        ;

        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Product Categories Labels
            $this->fieldsFactory()->create(SPL_T_INLINE)
                ->identifier("categories_names")
                ->name("Categories Label")
                ->microData("http://schema.org/Product", "publicCategoryNames")
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
    public function getCategoriesFields(string $key, string $fieldName): void
    {
        switch ($fieldName) {
            //====================================================================//
            // Variant Readings
            case 'categories':
                //====================================================================//
                // Collect List of Categories Codes
                $categoriesCodes = array();
                /** @var Category $category */
                foreach ($this->object->getCategories() as $category) {
                    $categoriesCodes[] = $category->getCode();
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
    public function getCategoriesMultiLangFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Decode Multi-Lang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            //====================================================================//
            // READ Fields
            if ('categories_names' != $baseFieldName) {
                continue;
            }
            //====================================================================//
            // Collect List of Categories Labels
            $categoriesNames = array();
            /** @var Category $category */
            foreach ($this->object->getCategories() as $category) {
                /** @var CategoryTranslation $translation */
                $translation = $category->getTranslation($isoLang);
                $categoriesNames[] = $translation->getLabel();
            }

            $this->out[$fieldName] = InlineHelper::fromArray($categoriesNames);
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
    protected function setCategoriesFields(string $fieldName, ?string $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'categories':
                //====================================================================//
                // Create Categories Updater
                $updater = new CategoriesUpdater($this->object, $fieldData);
                //====================================================================//
                // Add Categories
                foreach ($updater->getAddedCodes() as $identifier) {
                    $category = $this->categoryRepository->findOneByIdentifier($identifier);
                    if ($category instanceof CategoryInterface) {
                        $this->object->addCategory($category);
                    }
                }
                //====================================================================//
                // Remove Categories
                foreach ($updater->getRemovedCodes() as $identifier) {
                    $category = $this->categoryRepository->findOneByIdentifier($identifier);
                    if ($category instanceof CategoryInterface) {
                        $this->object->removeCategory($category);
                    }
                }

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }

    /**
     * get Choices for Categories Codes
     *
     * @return array
     */
    private function getCategoriesChoices(): array
    {
        $choices = array();
        //====================================================================//
        // Filter Categories on Default Channel
        $rootCategoryId = $this->configuration->getRootCategoryId();
        if (empty($rootCategoryId)) {
            return $choices;
        }
        //====================================================================//
        // Get All Categories
        /** @var CategoryInterface[] $categories */
        $categories = $this->categoryRepository->findBy(array(
            'root' => (string) $rootCategoryId
        ));
        foreach ($categories as $category) {
            $choices[$category->getCode()] = sprintf(
                "[%s] %s",
                $category->getCode(),
                self::getCategoryFullName($category, $this->locales->getDefault())
            );
        }

        return $choices;
    }

    /**
     * Get Category Full Name String
     */
    private static function getCategoryFullName(CategoryInterface $category, string $isoCode): string
    {
        $parent = $category->getParent();
        $fullName = ($parent instanceof CategoryInterface)
            ? self::getCategoryFullName($parent, $isoCode)
            : ""
        ;
        /** @var CategoryTranslationInterface $translation */
        $translation = $category->getTranslation($isoCode);

        $fullName .= $category->isRoot() ? "" :  (" > ".$translation->getLabel());

        return $fullName;
    }
}
