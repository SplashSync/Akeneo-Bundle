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

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Exception;
use Splash\Akeneo\Objects\Product\Attributes\FilesTrait as FilesTrait;
use Splash\Models\Objects\DocumentsTrait as DocumentsTrait;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface as AkeneoProduct;

/**
 * Access to Product Documents Fields
 */
trait DocumentTrait
{
    use DocumentsTrait;
    use FilesTrait;


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
            ->inList("documents")
            ->name("Document")
            ->group($groupName)
            ->microData("http://schema.org/Product", "document")
            ->isNotTested();
        //====================================================================//
        // Product Documents => Label
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("label")
            ->inList("documents")
            ->name("Label")
            ->group($groupName)
            ->microData("http://schema.org/Product", "labelDocument")
            ->isNotTested();
        //====================================================================//
        // Product Documents => Position
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("position")
            ->inList("documents")
            ->name("Position")
            ->group($groupName)
            ->microData("http://schema.org/Product", "positionDocument")
            ->isNotTested();
    }

    /**
     * Read requested Document Data
     *
     * @param string $key Input List Key
     * @param string $fieldName Document Field Identifier / Name
     * @return void
     * @throws Exception
     */
    protected function getDocumentFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Load all Files Attributes
        $documents = $this->getAllDocuments($this->object);

        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "documents", $fieldName);
        if (!$fieldId) {
            return;
        }

        //====================================================================//
        // Walk on Files Attributes
        $index = 0;
        foreach ($documents as $document) {
            //====================================================================//
            // READ Fields
            switch ($fieldId) {
                //====================================================================//
                // DOCUMENTS INFORMATIONS
                //====================================================================//
                case 'document':
                    $value = $this->documents->getSplashDocument($document);
                    break;

                case 'position':
                    $value = $index;
                    break;

                case 'label':
                    $value = $document["label"];
                    break;

                default:
                    return;
            }
            self::lists()->insert($this->out, "documents", $fieldName, $index, $value);
            unset($this->in[$fieldName]);
            $index++;
        }
    }


    /**
     * @param AkeneoProduct $product
     *
     * @return array<Attribute de type Documents>
     * @throws Exception
     */
    private function getAllDocuments(AkeneoProduct $product): array
    {
        /** @var string[] $code * */
        $attributes = $this->attr->findByType(AttributeTypes::FILE);

        $index = 0;
        foreach ($attributes as $attribute) {
            $splDoc = $this->getFileValue($product, $attribute, $this->getParameter("locale", "en_US"), $this->configuration->getChannel());

            if ($splDoc) {
                $list[] = array(
                    "code" => $attribute->getCode(),
                    "label" => $attribute->getLabel(),
                    "position" => $index,
                    "document" => $splDoc,
                );

            }
            $index++;
        }

        return $list ?? array();
    }
}
