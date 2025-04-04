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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Webmozart\Assert\Assert;

/**
 * Handles operations related to association types and associated products.
 */
class AssociationManager
{
    public function __construct(
        private readonly AssociationTypeRepositoryInterface $repository,
        private readonly VariantsManager $variants,
    ) {
    }

    /**
     * @return AssociationTypeInterface[]
     */
    public function getAllTypes(): array
    {
        Assert::allIsInstanceOf(
            $associationTypes = $this->repository->findAll(),
            AssociationTypeInterface::class
        );

        return $associationTypes;
    }

    /**
     * Fetch List of Associated Product for Association Type
     *
     * @return ArrayCollection<ProductInterface>
     */
    public function getAssociatedProducts(ProductInterface $product, string $associationTypeCode): ArrayCollection
    {
        $productIds = new ArrayCollection();
        //====================================================================//
        // Fetch List of Associated Products
        $assoProducts = $product->isVariant()
            ? $product->getParent()?->getAssociatedProducts($associationTypeCode)
            : $product->getAssociatedProducts($associationTypeCode)
        ;
        //====================================================================//
        // Walk on Associated Products
        foreach ($assoProducts ?? array() as $assoProduct) {
            $productIds->add($assoProduct);
        }
        //====================================================================//
        // Fetch List of Associated Products
        $assoModels = $product->isVariant()
            ? $product->getParent()?->getAssociatedProductModels($associationTypeCode)
            : $product->getAssociatedProductModels($associationTypeCode)
        ;
        //====================================================================//
        // Walk on Associated Product Models
        foreach ($assoModels ?? array() as $assoModel) {
            if (!$assoModel instanceof ProductModelInterface) {
                continue;
            }
            foreach ($this->variants->getModelProducts($assoModel, true) as $assoProduct) {
                $productIds->add($assoProduct);
            }
        }

        return $productIds;
    }
}
