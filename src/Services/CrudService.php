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

namespace   Splash\Akeneo\Services;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductRepository as Repository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilder as Builder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel as Model;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface as Familly;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface as Remover;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface as Saver;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface as Updater;
use ArrayObject;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Splash\Akeneo\Services\ModelsManager as Models;
use Splash\Akeneo\Services\VariantsManager as Variants;
use Splash\Core\SplashCore as Splash;
use Symfony\Component\Validator\Validator\RecursiveValidator as Validator;

/**
 * Akeneo Product CRUD Service
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CrudService
{
    use \Splash\Models\Objects\ObjectsTrait;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var Updater
     */
    private $updater;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Saver
     */
    private $saver;

    /**
     * @var Remover
     */
    private $remover;

    /**
     * @var Variants
     */
    private $variants;

    /**
     * @var Models
     */
    private $models;

    /**
     * Service  Constructor.
     *
     * @param Repository $repository
     * @param Builder    $builder
     * @param Updater    $updater
     * @param Validator  $validator
     * @param Saver      $saver
     * @param Remover    $remover
     * @param Variants   $variants
     * @param Models     $models
     */
    public function __construct(
        Repository $repository,
        Builder $builder,
        Updater $updater,
        Validator $validator,
        Saver $saver,
        Remover $remover,
        Variants $variants,
        Models $models
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->variants = $variants;
        $this->models = $models;
    }

    /**
     * Update Akeneo Product in database
     *
     * @param array|ArrayObject $inputs
     *
     * @return null|Product
     */
    public function createProduct($inputs): ?Product
    {
        try {
            //====================================================================//
            // Try Identify Product Family Variant
            $familyVariant = $this->getProductFamily($inputs);
            //====================================================================//
            // Try Identify Root Product Model
            $rootProduct = $this->getRootProduct($inputs);
            //====================================================================//
            // Check if Ready to Create New Product
            if (!$this->isReadyToCreate($inputs, $familyVariant)) {
                return null;
            }
            //====================================================================//
            // Detect Family
            $family = null;
            if ($familyVariant) {
                $family = $familyVariant->getFamily();
            }
            //====================================================================//
            // Create a New PIM Product
            $product = $this->builder->createProduct(
                null,
                $family ? $family->getCode() : null
            );
            //====================================================================//
            // Setup Product Family Variant
            if ($familyVariant) {
                $product->setFamilyVariant($familyVariant);
                //====================================================================//
                // Resolve Product Model
                $productModel = $this->models->resolveParent($inputs, $familyVariant, $rootProduct);
                //====================================================================//
                // Setup Product Parent Model
                if ($productModel) {
                    $productModel->addProduct($product);
                }
            }

            Splash::Log()->Msg("Akeneo Product Created");
        } catch (Exception $e) {
            Splash::Log()->Err($e->getMessage());
            Splash::Log()->Err($e->getTraceAsString());

            return null;
        }
        //====================================================================//
        // Return a New Object
        return  $product;
    }

    /**
     * Update Akeneo Product in database
     *
     * @param Product $product
     *
     * @return bool
     */
    public function update(Product &$product): bool
    {
        try {
            //====================================================================//
            // Validate Changes
            $this->validator->validate($product);
            //====================================================================//
            // Save Changes
            $this->saver->save($product);

            Splash::Log()->Msg("Akeneo Product Updated - ".$product->getId());
        } catch (\Exception $e) {
            Splash::Log()->Err("Akeneo Product Update Failed");

            return Splash::Log()->Err($e->getMessage());
        }
        //====================================================================//
        // Return Object Id
        return  true;
    }

    /**
     * Remove Product from Database
     *
     * @param Product $product
     *
     * @return bool
     */
    public function delete(Product $product): bool
    {
        try {
            $productModel = $product->getParent();
            $this->remover->remove($product);
            if ($productModel) {
                $this->models->delete($productModel);
            }
        } catch (EntityNotFoundException $e) {
            return true;
        } catch (Exception $e) {
            Splash::Log()->Err("Akeneo Product Delete Failed");

            return Splash::Log()->Err($e->getMessage());
        }
        Splash::Log()->Msg("Akeneo Product Deleted");

        return true;
    }

    /**
     * Identify New product Family
     *
     * @param array|ArrayObject $inputs
     *
     * @return null|Familly
     */
    private function getProductFamily($inputs): ?Familly
    {
        //====================================================================//
        // If family Code is Given
        if (isset($inputs["family_code"]) && !empty($inputs["family_code"])) {
            return $this->variants->findFamilyVariantByCode($inputs["family_code"]);
        }

        //====================================================================//
        // If Attributes are Given
        if (isset($inputs["attributes"]) && is_iterable($inputs["attributes"])) {
            return $this->variants->findFamilyVariantByAttributes($inputs["attributes"]);
        }

        return null;
    }

    /**
     * Identify New Product Parent Model
     *
     * @param array|ArrayObject $inputs
     *
     * @return null|Model
     */
    private function getRootProduct($inputs): ?Model
    {
        //====================================================================//
        // If NO Variants Given
        if (!isset($inputs["variants"]) || !is_iterable($inputs["variants"])) {
            return null;
        }
        //====================================================================//
        // Walk on Existing Variants
        foreach ($inputs["variants"] as $variant) {
            //====================================================================//
            // Check Product Id is here
            if (!isset($variant["id"]) || !is_string($variant["id"])) {
                continue;
            }
            //====================================================================//
            // Extract Variable Product Id
            $variantProductId = self::objects()->id($variant["id"]);
            if (false !== $variantProductId) {
                return $this->getParentModel($variantProductId);
            }
        }

        return null;
    }

    /**
     * Recursive Reading of Product Parent Id
     *
     * @param string $productId
     *
     * @return null|Model
     */
    private function getParentModel(string $productId): ?Model
    {
        //====================================================================//
        // Load Product from Repository
        $product = $this->repository->find($productId);
        if (!($product instanceof Product)) {
            return null;
        }

        do {
            //====================================================================//
            // LOAD PARENT
            $parent = $product->getParent();
            //====================================================================//
            // PRODUCT HAS NO PARENTS
            if (null === $parent) {
                return ($product instanceof Model) ? $product : null;
            }
            //====================================================================//
            // PRODUCT HAS PARENTS
            $product = $parent;
        } while (null !== $parent);
    }

    /**
     * Verify if Ok to Create a New Product
     *
     * @param array|ArrayObject $inputs
     * @param null|Familly      $family
     *
     * @return bool
     */
    private function isReadyToCreate($inputs, $family = null): bool
    {
        //====================================================================//
        // Verify Product Sku is Given
        if (!isset($inputs["sku"]) || empty($inputs["sku"])) {
            return Splash::log()->errTrace("No SKU Given for new Product");
        }

        //====================================================================//
        // If Attributes are Given
        if (isset($inputs["attributes"]) && !empty($inputs["attributes"])) {
            if (!($family instanceof Familly)) {
                Splash::log()->www("Inputs", $inputs);

                return Splash::log()->errTrace("No Family Variant identified for new Product");
            }
        }

        return true;
    }
}
