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

namespace Splash\Akeneo\Objects\Product;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Splash\Models\Helpers\ObjectsHelper;

trait AssociationsTrait
{
    /**
     * Build Fields using FieldFactory
     */
    public function buildAssociatedFields(): void
    {
        //====================================================================//
        // Walk on Products Associations Types
        foreach ($this->association->getAllTypes() as $associationType) {
            //====================================================================//
            // Product Association ID
            $this->fieldsFactory()->create((string) self::objects()->encode("Product", SPL_T_ID))
                ->identifier("id")
                ->name("ID")
                ->inList($this->getAssociatedListId($associationType))
                ->microData("http://schema.org/Product", strtolower($associationType->getCode()))
                ->isReadOnly()
            ;
            //====================================================================//
            // Product Association Sku
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("sku")
                ->name("SKU")
                ->inList($this->getAssociatedListId($associationType))
                ->microData("http://schema.org/Product", strtolower($associationType->getCode())."Sku")
                ->isReadOnly()
            ;
        }
    }

    /**
     * Read requested Field
     */
    public function getAssociatedFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Walk on Products Associations Types
        foreach ($this->association->getAllTypes() as $associationType) {
            $listId = $this->getAssociatedListId($associationType);
            //====================================================================//
            // Check if List field & Init List Array
            $fieldId = self::lists()->initOutput($this->out, $listId, $fieldName);
            if (!$fieldId) {
                continue;
            }
            //====================================================================//
            // Walk on Products IDs
            $products = $this->association->getAssociatedProducts($this->object, $associationType->getCode());
            foreach ($products as $index => $product) {
                //====================================================================//
                // Prepare
                switch ($fieldId) {
                    case "id":
                        $value = ObjectsHelper::encode(
                            "Product",
                            $product->getUuid()->toString()
                        );

                        break;
                    case "sku":
                        $value = $product->getReference();

                        break;
                    default:
                        return;
                }
                //====================================================================//
                // Insert Data in List
                self::lists()->insert($this->out, $listId, $fieldId, $index, $value);
            }

            unset($this->in[$key]);
        }
    }

    /**
     * Get Association type List Name
     */
    private function getAssociatedListId(AssociationTypeInterface $associationType): string
    {
        return sprintf("asso_%s", strtolower($associationType->getCode()));
    }
}
