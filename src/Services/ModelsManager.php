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

/**
 * @abstract    Akeneo Bundle Data Transformer for Splash Bundle
 *
 * @author      B. Paquier <contact@splashsync.com>
 */

namespace   Splash\Akeneo\Services;

use Akeneo\Component\StorageUtils\Factory\SimpleFactory as Builder;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface as Remover;
use Akeneo\Component\StorageUtils\Saver\SaverInterface as Saver;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface as Updater;
use ArrayObject;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductModelRepository as Repository;
use Pim\Component\Catalog\Model\FamilyVariantInterface as Familly;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductModel as Model;
use Splash\Akeneo\Services\VariantsManager as Variants;
use Splash\Core\SplashCore as Splash;
use Symfony\Component\Validator\Validator\RecursiveValidator as Validator;

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
     * @var RecursiveValidator
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
     * @param Repository $repository
     * @param Builder    $builder
     * @param Updater    $updater
     * @param Validator  $validator
     * @param Saver      $saver
     * @param Remover    $remover
     */
    public function __construct(Repository $repository, Builder $builder, Updater $updater, Validator $validator, Saver $saver, Remover $remover, Variants $variants)
    {
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
    public function resolveParent(iterable $inputs, Familly $familyVariant, Model $model = null): ?Model
    {
        //====================================================================//
        // If No Root Product Model Given
        if (!$model) {
            $model = $this->builder->create();
            $model->setLevel(0);
            $model->setFamilyVariant($familyVariant);
            $model->setCode($inputs["sku"]."-model");
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
