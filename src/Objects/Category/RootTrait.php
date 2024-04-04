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

/**
 * Category Root Fields Access
 */
trait RootTrait
{
    //====================================================================//
    // PRODUCT CORE INFOS
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     */
    public function buildRootFields(): void
    {
        //====================================================================//
        // Root Category Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("root_code")
            ->name('Root Code')
            ->group("Parents")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    public function getRootFields(string $key, string $fieldName): void
    {
        switch ($fieldName) {
            case 'root_code':
                $root = null;
                if ($rootId = $this->object->getRoot()) {
                    $root = $this->repository->find($rootId);
                }
                $this->out[$fieldName] = $root?->getCode();

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }
}
