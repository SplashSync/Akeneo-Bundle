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

use Splash\Core\SplashCore as Splash;
use Pim\Component\Catalog\Model\Product;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface as Remover;
use Akeneo\Component\StorageUtils\Saver\SaverInterface as Saver;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface as Updater;
use Pim\Component\Catalog\Builder\ProductBuilder as Builder;
use Symfony\Component\Validator\Validator\RecursiveValidator as Validator;

class CrudService
{
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
     * @param Builder   $builder
     * @param Updater   $updater
     * @param Validator $validator
     * @param Saver     $saver
     * @param Remover   $remover
     */
    public function __construct(Builder $builder, Updater $updater, Validator $validator, Saver $saver, Remover $remover)
    {
        $this->builder = $builder;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->remover = $remover;
    }


    /**
     * Update Akeneo Product in database
     * 
     * @param Product $product
     * @return bool
     */
    public function update(Product &$product): bool
    {
        try {
//            //====================================================================//
//            // If Product is New => Disable Doctrine postUpdate Event
//            // This is done to prevent Repeated Commits (Create + Update)
//            if (!$Object->getId()) {
//                Splash::Local()->setListnerState("postUpdate", false);
//            }
//            //====================================================================//
//            // Re-Attach Product to Entity Manager
//            $AttachedObject = $this->Attach($Object);
            //====================================================================//
            // Validate Changes
            $this->validator->validate($product);
            //====================================================================//
            // Save Changes
            $this->saver->save($product);

            Splash::Log()->Msg("Akeneo Product Update Done");
            Splash::Log()->Msg("Updated => ".$product->getId());
        } catch (\Exception $e) {
            Splash::Log()->Err("Akeneo Product Update Failed");

            return Splash::Log()->Err($e->getMessage());
        }
//        //====================================================================//
//        // Whatever => Enable Doctrine postUpdate Event Again!
//        Splash::Local()->setListnerState("postPersist", true);
//        Splash::Local()->setListnerState("postUpdate", true);
        //====================================================================//
        // Return Object Id
        return  true;
    }
}
