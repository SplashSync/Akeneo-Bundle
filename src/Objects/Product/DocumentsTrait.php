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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as AkeneoProduct;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Exception;

/**
 * Access to Product Documents Fields
 */
trait DocumentsTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildDocumentFields(): void
    {
        //====================================================================//
        // PRODUCT DOCUMENTS
        //====================================================================//

        $groupName = "Documents";

        //====================================================================//
        // Product Documents List
        $this->fieldsFactory()->create(SPL_T_FILE)
            ->identifier("document")
            ->name("Document")
            ->inList("documents")
            ->group($groupName)
            ->microData("https://schema.org/DigitalDocument", "document")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Documents => Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("code")
            ->inList("documents")
            ->name("Code")
            ->group($groupName)
            ->microData("https://schema.org/DigitalDocument", "identifier")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Documents => Label
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("label")
            ->inList("documents")
            ->name("Label")
            ->group($groupName)
            ->microData("https://schema.org/DigitalDocument", "name")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Documents => Position
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("position")
            ->inList("documents")
            ->name("Position")
            ->group($groupName)
            ->microData("https://schema.org/DigitalDocument", "position")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Document Data
     *
     * @param string $key       Input List Key
     * @param string $fieldName Document Field Identifier / Name
     *
     * @throws Exception
     *
     * @return void
     */
    protected function getDocumentFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "documents", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Load all Files Attributes
        $documents = $this->getAllDocuments($this->object);
        //====================================================================//
        // Walk on Files Attributes
        $index = 0;
        foreach ($documents as $document) {
            //====================================================================//
            // READ Fields
            switch ($fieldId) {
                //====================================================================//
                // DOCUMENTS INFORMATION
                //====================================================================//
                case 'document':
                    $value = $document["document"];

                    break;
                case 'position':
                    $value = $index;

                    break;
                case 'code':
                    $value = $document["code"];

                    break;
                case 'label':
                    $value = $document["label"];

                    break;
                default:
                    return;
            }
            self::lists()->insert($this->out, "documents", $fieldName, $index, $value);
            $index++;
        }
        unset($this->in[$key]);
    }

    /**
     * @param AkeneoProduct $product
     *
     * @throws Exception
     *
     * @return array<int<0, max>,array{code: string, label: null|string, position: int<0, max>, document: mixed}>
     */
    private function getAllDocuments(AkeneoProduct $product): array
    {
        $attributes = $this->attr->findByType(AttributeTypes::FILE);

        $index = 0;
        foreach ($attributes as $attribute) {
            $splDoc = $this->attr->get($product, $attribute->getCode());
            if ($document = $splDoc[$attribute->getCode()] ?? null) {
                $defaultTranslation = $this->locales->getDefault();
                $label = null;

                if (null !== $defaultTranslation) {
                    $translation = $attribute->getTranslation($defaultTranslation);

                    $label = $translation?->getLabel();
                }
                $list[] = array(
                    "code" => $attribute->getCode(),
                    "label" => $label,
                    "position" => $index,
                    "document" => $document,
                );
            }
            $index++;
        }

        return $list ?? array();
    }
}
