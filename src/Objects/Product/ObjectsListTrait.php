<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
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
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Doctrine\ORM\QueryBuilder;
use Splash\Client\Splash;

/**
 * Akeneo Product Objects Lists
 */
trait ObjectsListTrait
{
    use \Splash\Bundle\Helpers\Doctrine\ObjectsListHelperTrait;

    /**
     * Configure Query Builder beforer List Queries
     *
     * @param QueryBuilder $queryBuilder
     *
     * @return self
     */
    protected function configureObjectListQueryBuilder(QueryBuilder $queryBuilder): self
    {
        //====================================================================//
        // Get List of Categories for this Connection
        $categoryCodes = $this->getParameter("categories", array());
        if (!is_array($categoryCodes) || empty($categoryCodes)) {
            return $this;
        }
        //====================================================================//
        // Connect to Category Repository
        /** @var CategoryRepository $repository */
        $repository = $queryBuilder->getEntityManager()->getRepository(Category::class);
        //====================================================================//
        // Load Parent Categories
        $categories = $repository->getCategoriesByCodes($categoryCodes);
        //====================================================================//
        // Collect List of Sub-Categories Ids
        $childCategoryIds = array();
        /** @var CategoryInterface $categorie */
        foreach ($categories as $categorie) {
            $childCategoryIds = array_merge(
                $childCategoryIds,
                array($categorie->getid()),
                $repository->getAllChildrenIds($categorie)
            );
        }
        //====================================================================//
        // Setup QueryBuilder
        Splash::log()->war('List Filtered on '.count($childCategoryIds)." Categories");
        $queryBuilder
            ->innerJoin('c.categories', 'cat')
            ->andWhere($queryBuilder->expr()->in('cat.id', ":categories"))
            ->setParameter('categories', $childCategoryIds)
        ;

        return $this;
    }

    /**
     * Setup Filters for List Query Builder
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $filter
     *
     * @return self
     */
    protected function setObjectListFilter(QueryBuilder $queryBuilder, string $filter): self
    {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->like('c.identifier', ":filter")
        );
        $queryBuilder->setParameter('filter', $filter);

        return $this;
    }

    /**
     * Transform Product To List Array Data
     *
     * @param Product $variant
     *
     * @return array
     */
    protected function getObjectListArray(Product $variant): array
    {
//        $firstCat = $variant->getCategories()->first();
//        if ($firstCat) {
//            Splash::log()->www('First Categorie', $firstCat->getCode());
//            Splash::log()->www('Child Categories', count($firstCat->getChildren()));
//        }

        return array(
            'id' => $variant->getId(),
            'identifier' => $variant->getIdentifier(),
            'enabled' => $variant->isEnabled(),
            'variant' => $variant->isVariant(),
            'label' => $variant->getLabel(),
            'updated' => $variant->getUpdated()->format(SPL_T_DATETIMECAST),
        );
    }
}
