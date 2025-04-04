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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface as Model;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface as Family;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactory as Builder;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface as Remover;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface as Saver;
use ArrayObject;
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
     * Service  Constructor.
     *
     * @param Builder   $builder
     * @param Validator $validator
     * @param Saver     $saver
     * @param Remover   $remover
     */
    public function __construct(
        private readonly Builder $builder,
        private readonly Validator $validator,
        private readonly Saver $saver,
        private readonly Remover $remover
    ) {
    }

    /**
     * Identify New Product Parent
     *
     * @param array|ArrayObject $inputs
     * @param Family            $familyVariant
     * @param null|Model        $model
     *
     * @return null|Model
     */
    public function resolveParent($inputs, Family $familyVariant, Model $model = null): ?Model
    {
        $coreSku = isset($inputs["sku"]) ? $inputs["sku"] : uniqid();

        //====================================================================//
        // If No Root Product Model Given
        if (!$model) {
            /** @var Model $model */
            $model = $this->builder->create();
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
        } catch (Exception $e) {
            Splash::Log()->Err("Akeneo Product Model Delete Failed");

            return Splash::Log()->Err($e->getMessage());
        }

        return true;
    }
}
