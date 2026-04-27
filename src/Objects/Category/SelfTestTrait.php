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

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Splash\Client\Splash;

trait SelfTestTrait
{
    /**
     * SelfTest of Category Forest Tree
     */
    public function selftest(): bool
    {
        //====================================================================//
        // Get List of Categories for this Connection
        $categoryCodes = $this->getParameter("categories", array());
        if (!is_array($categoryCodes) || empty($categoryCodes)) {
            return true;
        }
        //====================================================================//
        // Safety Check
        if (!$this->repository instanceof NestedTreeRepository) {
            return true;
        }
        //====================================================================//
        // Load Parent Categories
        $categories = $this->repository->getCategoriesByCodes($categoryCodes);
        //====================================================================//
        // Verify All Categories Tree
        /** @var CategoryInterface $categorie */
        foreach ($categories as $categorie) {
            //====================================================================//
            // Verify All Categories Tree
            $errors = $this->repository->verify(array(
                "treeRootNode" => $categorie,
            ));
            //====================================================================//
            // No Errors Found
            if (!is_array($errors)) {
                continue;
            }
            //====================================================================//
            // Display Errors
            Splash::log()->war(
                sprintf("Category %s has %d errors", $categorie->getCode(), count($errors))
            );
            Splash::log()->dump($errors);

            return false;
        }

        return true;
    }
}
