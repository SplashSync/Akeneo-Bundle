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
 * Category Parent Fields Access
 */
trait ParentTrait
{
    //====================================================================//
    // PRODUCT CORE INFOS
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    public function buildParentFields(): void
    {
        //====================================================================//
        // Parent Category
        $this->fieldsFactory()->create((string) self::objects()->encode("Category", SPL_T_ID))
            ->identifier("parent")
            ->name('Parent')
            ->group("Parents")
            ->microData("http://schema.org/ProductCollection", "isPartOf")
            ->isRequired()
        ;
        //====================================================================//
        // Category Position
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("position")
            ->name('Position')
            ->microData("http://schema.org/ProductCollection", "position")
            ->isReadOnly()
        ;
        //====================================================================//
        // Parent Category Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("parent_code")
            ->name('Parent Code')
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
    public function getParentFields(string $key, string $fieldName): void
    {
        switch ($fieldName) {
            case 'parent':
                $parent = $this->object->getParent();
                $this->out[$fieldName] = $parent
                    ? self::objects()->encode("Category", (string) $parent->getId())
                    : null
                ;

                break;
            case 'position':
                $this->out[$fieldName] = $this->positionResolver->getPosition($this->object);

                break;
            case 'parent_code':
                $this->out[$fieldName] = $this->object->getParent()?->getCode();

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string      $fieldName Field Identifier / Name
     * @param null|string $fieldData Field Data
     *
     * @return void
     */
    protected function setParentFields(string $fieldName, ?string $fieldData): void
    {
        switch ($fieldName) {
            case 'parent':
                $parentId = self::objects()->id((string) $fieldData);
                if ($parentId == $this->object->getParent()?->getId()) {
                    break;
                }
                $this->object->setParent($this->load((string) $parentId));
                $this->needUpdate();

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
