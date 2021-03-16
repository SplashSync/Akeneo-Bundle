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

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductModelRepository as Repository;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface as Model;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface as Familly;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactory as Builder;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface as Remover;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface as Saver;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface as Updater;
use ArrayObject;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Splash\Akeneo\Services\VariantsManager as Variants;
use Splash\Core\SplashCore as Splash;
use Symfony\Component\Validator\Validator\RecursiveValidator as Validator;

/**
 * Akeneo Bundle Product Models Manager
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModelsManager
{
    /**
     * @var Variants
     */
    protected $variants;
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
     * Service  Constructor.
     *
     * @param Repository $repository
     * @param Builder    $builder
     * @param Updater    $updater
     * @param Validator  $validator
     * @param Saver      $saver
     * @param Remover    $remover
     * @param Variants   $variants
     */
    public function __construct(
        Repository $repository,
        Builder $builder,
        Updater $updater,
        Validator $validator,
        Saver $saver,
        Remover $remover,
        Variants $variants
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->variants = $variants;
    }

    /**
     * Identify New Product Parent
     *
     * @param array|ArrayObject $inputs
     * @param Familly           $familyVariant
     * @param null|Model        $model
     *
     * @return null|Model
     */
    public function resolveParent($inputs, Familly $familyVariant, Model $model = null): ?Model
    {
        $coreSku = isset($inputs["sku"]) ? $inputs["sku"] : uniqid();

        //====================================================================//
        // If No Root Product Model Given
        if (!$model) {
            $model = $this->builder->create();
            $model->setLevel(0);
            $model->setFamilyVariant($familyVariant);
            $model->setCode($coreSku."-model");
            //====================================================================//
            // Validate Changes
            $this->validator->validate($model);
            //====================================================================//
            // Save Changes
            $this->saver->save($model);
        }

        //$familyVariant->getLevelForAttributeCode($attributeCode)

//        //====================================================================//
//        // If family Code is Given
//        if(isset($inputs["family_code"]) && !empty($inputs["family_code"])) {
//            return $this->variants->findFamilyVariantByCode($inputs["family_code"]);
//        }
//
//        //====================================================================//
//        // If Attributes are Given
//        if(isset($inputs["attributes"]) && is_iterable($inputs["attributes"])) {
//            return $this->variants->findFamilyVariantByAttributes($inputs["attributes"]);
//        }

        return $model;
    }

    /**
     * Remove Product Model from Database
     *
     * @param Model $productModel
     *
     * @return bool
     */
    public function delete(Model $productModel): bool
    {
        try {
            if (0 == count($productModel->getProducts())) {
                $this->remover->remove($productModel);
            }
        } catch (EntityNotFoundException $e) {
            return true;
        } catch (Exception $e) {
            Splash::Log()->Err("Akeneo Product Model Delete Failed");

            return Splash::Log()->Err($e->getMessage());
        }

        return true;
    }
}
