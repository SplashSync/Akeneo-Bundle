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

namespace Splash\Akeneo\Objects\Category;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface as Category;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslationInterface;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Doctrine\ORM\QueryBuilder;
use Splash\Bundle\Helpers\Doctrine\ObjectsListHelperTrait;

/**
 * Akeneo Category Objects Lists
 */
trait ObjectsListTrait
{
    use ObjectsListHelperTrait;

    /**
     * Configure Query Builder before List Queries
     *
     * @param QueryBuilder $queryBuilder
     *
     * @return self
     */
    protected function configureObjectListQueryBuilder(QueryBuilder $queryBuilder): self
    {
        //====================================================================//
        // Filter Categories on Default Channel
        $rootCategoryId = $this->configuration->getRootCategoryId();
        if ($rootCategoryId) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->like('c.root', ":channel")
            );
            $queryBuilder->setParameter('channel', (string) $rootCategoryId);
        }

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
            $queryBuilder->expr()->like('c.code', ":filter")
        );
        $queryBuilder->setParameter('filter', $filter);

        return $this;
    }

    /**
     * Transform Category To List Array Data
     *
     * @param Category $category
     *
     * @return array
     */
    protected function getObjectListArray(Category $category): array
    {
        /** @var CategoryTranslationInterface $translation */
        $translation = $category->getTranslation($this->locales->getDefault());

        return array(
            'id' => $category->getId(),
            'code' => $category->getCode(),
            'label' => $translation->getLabel(),
            'updated' => $category->getUpdated()->format(SPL_T_DATETIMECAST),
        );
    }
}
