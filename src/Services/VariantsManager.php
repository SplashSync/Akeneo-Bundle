<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Akeneo\Services;

use ArrayObject;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyVariantRepository as Variants;
use Pim\Component\Catalog\Model\AttributeInterface as Attribute;
use Pim\Component\Catalog\Model\FamilyVariantInterface as Familly;
use Pim\Component\Catalog\Model\ProductInterface as Product;
use Pim\Component\Catalog\Model\ProductModelInterface as ProductModel;
use Splash\Core\SplashCore as Splash;

/**
 * Akeneo Product Variants Informations Access
 */
class VariantsManager
{
    use \Splash\Models\Objects\ObjectsTrait;

    /**
     * @var LocalesManager
     */
    private $locales;

    /**
     * @var Variants
     */
    private $repository;

    /**
     * Cache for Family Variants Attributes Sets
     *
     * @var array
     */
    private static $varAttrs = array();

    /**
     * Service Constructor
     *
     * @param LocalesManager $locales
     */
    public function __construct(LocalesManager $locales, Variants $repository)
    {
        //====================================================================//
        // Link to Splash Locales Manager
        $this->locales = $locales;
        //====================================================================//
        // Link to Akeneo Family Variants Repository
        $this->repository = $repository;
    }

    //====================================================================//
    // PRODUCT VARIANT METADATA
    //====================================================================//

    /**
     * Reading of Product Parent Id
     *
     * @param Product $product
     *
     * @return string
     */
    public function getParentModelId(Product $product): ?string
    {
        //====================================================================//
        // LOAD PRODUCT MAIN PARENT MODEL
        $parentModel = $this->getParentModel($product);
        //====================================================================//
        // PRODUCT HAS NO PARENTS
        if ($parentModel instanceof ProductModel) {
            return self::objects()->encode("Product", $parentModel->getId());
        }

        return null;
    }

    /**
     * Recursive Reading of Product Parent Id
     *
     * @param Product $product
     *
     * @return string
     */
    public function getVariantsList(Product $product): array
    {
        //====================================================================//
        // LOAD PRODUCT MAIN PARENT MODEL
        $parentModel = $this->getParentModel($product);
        //====================================================================//
        // PRODUCT HAS NO PARENTS
        if (!($parentModel instanceof ProductModel)) {
            return array();
        }
        //====================================================================//
        // PRODUCT PARENT MODEL
        return $this->getModelProducts($parentModel);
    }

    /**
     * Recursive Reading of Product Model Child Products
     *
     * @param ProductModel $model
     *
     * @return string
     */
    public function getModelProducts(ProductModel $model): array
    {
        $response = array();
        //====================================================================//
        // PRODUCT MODEL HAS CHILD MODELS
        if ($model->hasProductModels()) {
            foreach ($model->getProductModels() as $productModel) {
                $response = array_replace_recursive(
                    $response,
                    $this->getModelProducts($productModel)
                );
            }

            return $response;
        }

        //====================================================================//
        // PRODUCT MODEL HAS CHILD PRODUCTS
        $products = $model->getProducts();
        if (count($products) > 0) {
            foreach ($products as $product) {
                $response[$product->getId()] = array(
                    "id" => self::objects()->encode("Product", $product->getId()),
                    "rawId" => $product->getId(),
                    "sku" => $product->getIdentifier(),
                );
            }

            return $response;
        }

        return $response;
    }

    //====================================================================//
    // PRODUCT VARIANT FAMILY IDENTIFICATION
    //====================================================================//

    /**
     * Find Product Family Variant by Code
     *
     * @param string $familyCode
     *
     * @return null|Familly
     */
    public function findFamilyVariantByCode(string $familyCode): ?Familly
    {
        return $this->repository->findOneByIdentifier($familyCode);
    }

    /**
     * Find Product Family Variant by Code
     *
     * @param array|ArrayObject $attributes
     *
     * @return null|Familly
     */
    public function findFamilyVariantByAttributes(iterable $attributes): ?Familly
    {
        //====================================================================//
        // Walk on Each Product Attribute to Collect Codes
        $axes = array();
        foreach ($attributes as $attribute) {
            $axes[] = $attribute["code"];
        }

        //====================================================================//
        // Walk on Each Available Family Variants
        /** @var Familly $familyVariant */
        foreach ($this->repository->findAll() as $familyVariant) {
            $familyAxes = $this->getFamilyVariationAttributes($familyVariant);
            //====================================================================//
            // Family Variants has Same Attributes
            if (array_values($familyAxes) == $axes) {
                return $familyVariant;
            }
        }

        return null;
    }

    /**
     * Get List of Product Family Variants for Selector
     *
     * @return null|Familly
     */
    public function getFamilyChoices(): array
    {
        $choices = array();
        //====================================================================//
        // Walk on Each Available Family Variants
        /** @var Familly $familyVariant */
        foreach ($this->repository->findAll() as $familyVariant) {
            $familyVariant->setLocale($this->locales->getDefault());
            $choices[$familyVariant->getCode()] = $familyVariant->getTranslation()->getLabel();
        }

        return $choices;
    }

    //====================================================================//
    // PRODUCT VARIANT CORE FUNCTIONS
    //====================================================================//

    /**
     * Check if Attribute is a Variant Attributes
     *
     * @return array
     */
    public function isVariantAttribute(Product $product, string $fieldName): bool
    {
        $varAttrs = $this->getVariantAttributes($product);

        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Decode Multilang Field Name
            $baseFieldName = $this->locales->decode($fieldName, $isoLang);
            if (null == $baseFieldName) {
                continue;
            }
            //====================================================================//
            // Check if Base Field Name Exists
            if (in_array($baseFieldName, $varAttrs, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Product Variant Attributes Codes
     *
     * @return array
     */
    public function getVariantAttributes(Product $product): array
    {
        //====================================================================//
        // Load Product Familly Variant
        $familly = $product->getFamilyVariant();
        if (!$familly) {
            return array();
        }

        //====================================================================//
        // Fetch Familly Variants Attributes
        return $this->getFamilyVariationAttributes($familly);
    }

    /**
     * Recursive Reading of Product Parent Id
     *
     * @param Product $product
     *
     * @return string
     */
    private function getParentModel(Product $product): ?ProductModel
    {
        do {
            //====================================================================//
            // LOAD PARENT
            $parent = $product->getParent();
            //====================================================================//
            // PRODUCT HAS NO PARENTS
            if (null === $parent) {
                return ($product instanceof ProductModel) ? $product : null;
            }
            //====================================================================//
            // PRODUCT HAS PARENTS
            $product = $parent;
        } while (null !== $parent);
    }

    /**
     * Get List of All Attributes with Simple Caching
     *
     * @return array
     */
    private function getFamilyVariationAttributes(Familly $familly): array
    {
        $famillyId = $familly->getId();

        if (!isset(static::$varAttrs[$famillyId])) {
            //====================================================================//
            // Init Result
            $attrSet = array();
            //====================================================================//
            // Walk on All Variation Attributes Sets
            /** @var Attribute $attribute */
            foreach ($familly->getAxes() as $attribute) {
                $attrSet[$attribute->getId()] = $attribute->getCode();
            }
            //====================================================================//
            // Init Familly Variants Attributes Cache
            static::$varAttrs[$famillyId] = $attrSet;
        }

        return static::$varAttrs[$famillyId];
    }
}
