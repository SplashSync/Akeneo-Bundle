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

namespace Splash\Akeneo\Services;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface as ProductModel;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyVariantRepository as Variants;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as Attribute;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface as Family;
use Splash\Core\SplashCore as Splash;
use Splash\Models\Objects\ObjectsTrait;
use TypeError;

/**
 * Akeneo Product Variants Information Access
 */
class VariantsManager
{
    use ObjectsTrait;

    /**
     * Cache for Family Variants Attributes Sets
     *
     * @var null|array
     */
    private ?array $varAttrs = array();

    /**
     * Service Constructor
     */
    public function __construct(
        private readonly Variants $repository,
        private readonly Configuration $conf,
        private readonly LocalesManager $locales,
    ) {
    }

    //====================================================================//
    // PRODUCT VARIANT METADATA
    //====================================================================//

    /**
     * Reading of Product Parent ID
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
            return (string) self::objects()->encode("Product", (string) $parentModel->getId());
        }

        return null;
    }

    /**
     * Recursive Reading of Product Parent ID
     *
     * @param Product $product  Current Product Entity
     * @param bool    $entities Get Entities or Info Array
     *
     * @return array
     */
    public function getVariantsList(Product $product, bool $entities = false): array
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
        return $this->getModelProducts($parentModel, $entities);
    }

    /**
     * Recursive Reading of Product Model Child Products
     *
     * @param ProductModel $model    Current Product Model Entity
     * @param bool         $entities Get Entities or Info Array
     *
     * @return array[]|Product[]
     */
    public function getModelProducts(ProductModel $model, bool $entities = false): array
    {
        $response = array();
        //====================================================================//
        // PRODUCT MODEL HAS CHILD MODELS
        if ($model->hasProductModels()) {
            /** @var ProductModel $productModel */
            foreach ($model->getProductModels() as $productModel) {
                $response = array_merge_recursive(
                    $response,
                    $this->getModelProducts($productModel, $entities)
                );
            }

            return $response;
        }
        //====================================================================//
        // PRODUCT MODEL HAS CHILD PRODUCTS
        /** @var Product[] $products */
        $products = $model->getProducts();
        if (0 == count($products)) {
            return $response;
        }
        //====================================================================//
        // WALK ON MODEL CHILD PRODUCTS
        foreach ($products as $index => $product) {
            $productUuid = $product->getUuid()->toString();
            if (!$productUuid) {
                continue;
            }
            $response[$index] = $entities
                ? $product
                : array(
                    "id" => self::objects()->encode("Product", $productUuid),
                    "rawId" => $productUuid,
                    "sku" => $product->getIdentifier(),
                )
            ;
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
     * @return null|Family
     */
    public function findFamilyVariantByCode(string $familyCode): ?Family
    {
        /** @phpstan-ignore-next-line */
        return $this->repository->findOneByIdentifier($familyCode);
    }

    /**
     * Find Product Family Variant by Code
     *
     * @param array $attributes
     *
     * @return null|Family
     */
    public function findFamilyVariantByAttributes(array $attributes): ?Family
    {
        //====================================================================//
        // Walk on Locales
        foreach ($this->getWriteIsoLangs() as $isoLang) {
            //====================================================================//
            // Encode Attribute Axes
            if (!$axes = $this->getAttributesAxes($attributes, $isoLang)) {
                continue;
            }
            //====================================================================//
            // Walk on Each Available Family Variants
            /** @var Family $familyVariant */
            foreach ($this->repository->findAll() as $familyVariant) {
                $familyAxes = $this->getFamilyVariationAttributes($familyVariant, $isoLang);
                //====================================================================//
                // Family Variants has Same Attributes
                if (array_values($familyAxes) == $axes) {
                    return $familyVariant;
                }
            }
        }

        return null;
    }

    /**
     * Get List of Product Family Variants for Selector
     *
     * @return array
     */
    public function getFamilyChoices(): array
    {
        $choices = array();
        //====================================================================//
        // Walk on Each Available Family Variants
        /** @var Family $familyVariant */
        foreach ($this->repository->findAll() as $familyVariant) {
            $familyVariant->setLocale($this->locales->getDefault());
            /** @var FamilyTranslationInterface $familyTranslation */
            $familyTranslation = $familyVariant->getTranslation();

            try {
                $choices[$familyVariant->getCode()] = $familyTranslation->getLabel();
            } catch (TypeError $e) {
                $code = $familyVariant->getCode();
                $locale = $this->locales->getDefault();
                $choices[$code] = $code;
                Splash::log()->war("Family ".$code." has no Translation in ".$locale);
            }
        }

        return $choices;
    }

    //====================================================================//
    // PRODUCT VARIANT CORE FUNCTIONS
    //====================================================================//

    /**
     * Check if Attribute is a Variant Attributes
     *
     * @param Product $product
     * @param string  $fieldName
     *
     * @return bool
     */
    public function isVariantAttribute(Product $product, string $fieldName): bool
    {
        $varAttrs = $this->getVariantAttributes($product);

        //====================================================================//
        // Walk on Each Available Languages
        foreach ($this->locales->getAll() as $isoLang) {
            //====================================================================//
            // Decode Multi-lang Field Name
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
     * @param Product $product
     *
     * @return array
     */
    public function getVariantAttributes(Product $product): array
    {
        //====================================================================//
        // Load Product Family Variant
        $family = $product->getFamilyVariant();
        if (!$family) {
            return array();
        }

        //====================================================================//
        // Fetch Family Variants Attributes
        return $this->getFamilyVariationAttributes($family);
    }

    /**
     * Recursive Reading of Product Parent ID
     *
     * @param Product $product
     *
     * @return null|ProductModel
     */
    public function getParentModel(Product $product): ?ProductModel
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
        } while (true);
    }

    /**
     * Recursive Reading of All Product Parents
     *
     * @param Product $product
     *
     * @return ProductModel[]
     */
    public function getParentModels(Product $product): array
    {
        $parents = array();
        do {
            //====================================================================//
            // LOAD PARENT
            $parent = $product->getParent();
            //====================================================================//
            // PRODUCT HAS NO PARENTS
            if (null === $parent) {
                return $parents;
            }
            //====================================================================//
            // PRODUCT HAS PARENTS
            $parents[] = $parent;
            $product = $parent;
        } while (true);
    }

    /**
     * Recursive Reading of All Product Parents
     *
     * @param Product $product
     *
     * @return array<int|string, array|string>
     */
    public function getVariantsSummary(Product $product): array
    {
        //====================================================================//
        // Get Parent Model
        $model = $this->getParentModel($product);
        if (!$model) {
            return array($product->getUuid()->toString());
        }

        //====================================================================//
        // Get Children Uuids
        return array($model->getId() => self::getChildrenUuids($model));
    }

    /**
     * Get Available Iso Lang Codes
     *
     * @return array<null|string>
     */
    public function getWriteIsoLangs(): array
    {
        if ($this->conf->isLearningMode()) {
            return array_merge(array(null), $this->locales->getAll());
        }

        return array(null);
    }

    /**
     * Get Attribute Array Key for Iso Lang
     *
     * @param null|string $isoLang Iso Lang for Attribute Code
     *
     * @return string
     */
    public function getWriteAttributeKey(?string $isoLang): string
    {
        if ($isoLang) {
            return $this->locales->isDefault($isoLang)
                ? "label"
                : sprintf("label_%s", $isoLang)
            ;
        }

        return 'code';
    }

    /**
     * Get Attributes Axes Summary
     *
     * @param array       $attributes Splash Products Attributes List
     * @param null|string $isoLang    Iso Lang for Attribute Code
     *
     * @return null|string[]
     */
    private function getAttributesAxes(array $attributes, ?string $isoLang): ?array
    {
        //====================================================================//
        // Encode Attribute Key
        $key = $this->getWriteAttributeKey($isoLang);
        //====================================================================//
        // Walk on Each Product Attribute to Collect Codes
        $axes = array();
        foreach ($attributes as $attribute) {
            $axes[] = $attribute[$key];
        }

        return empty(array_filter($axes)) ? null : $axes;
    }

    /**
     * Get List of All Attributes with Simple Caching
     *
     * @param Family      $family
     * @param null|string $isoLang
     *
     * @return array
     */
    private function getFamilyVariationAttributes(Family $family, ?string $isoLang = null): array
    {
        $familyId = sprintf("%s-%s", $family->getId(), $isoLang ?? 'default');

        if (!isset($this->varAttrs[$familyId])) {
            //====================================================================//
            // Init Result
            $attrSet = array();
            //====================================================================//
            // Walk on All Variation Attributes Sets
            /** @var Attribute $attribute */
            foreach ($family->getAxes() as $attribute) {
                $attrSet[$attribute->getId()] = $isoLang
                    /** @phpstan-ignore-next-line  */
                    ? $attribute->getTranslation($isoLang)->getLabel()
                    : $attribute->getCode()
                ;
            }
            //====================================================================//
            // Init Family Variants Attributes Cache
            $this->varAttrs[$familyId] = $attrSet;
        }

        return $this->varAttrs[$familyId];
    }

    private static function getChildrenUuids(ProductModel $model): array
    {
        $uuids = array();
        //====================================================================//
        // Not Lowest Model => Recursive Scan
        $models = $model->getProductModels();
        if (!$models->isEmpty()) {
            /** @var ProductModel $childModel */
            foreach ($models as $childModel) {
                $uuids[$childModel->getId()] = self::getChildrenUuids($childModel);
            }
        } else {
            /** @var Product $product */
            foreach ($model->getProducts() as $product) {
                //====================================================================//
                // Last Level => Product
                $uuids[] = $product->getUuid()->toString();
            }
        }

        return $uuids;
    }
}
