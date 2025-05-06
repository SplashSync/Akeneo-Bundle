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
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslation;
use Exception;
use Splash\Models\Helpers\InlineHelper;

/**
 * Access to Product Documents Fields
 *
 * @phpstan-type DocumentArray array{
 *     code: string,
 *     label: null|string,
 *     position: int<0, max>,
 *     visible: bool,
 *     document: mixed,
 *     checksum: string,
 *     skus: string[],
 * }
 */
trait DocumentsTrait
{
    /**
     * Documents Information Cache
     *
     * @var null|array<string, DocumentArray>
     */
    private static ?array $documentsCache = null;

    /**
     * Empty Cache for Product Documents
     */
    public function clearDocumentsCache(): void
    {
        self::$documentsCache = null;
    }

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
        // Product Documents => Label
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("label_with_skus")
            ->inList("documents")
            ->name("Label with SKUs")
            ->group($groupName)
            ->microData("https://schema.org/DigitalDocument", "alternateName")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Documents => Skus
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("skus")
            ->inList("documents")
            ->name("References")
            ->group($groupName)
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
        //====================================================================//
        // Product Documents => Position
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("visible")
            ->inList("documents")
            ->name("Visible")
            ->group($groupName)
            ->microData("https://schema.org/DigitalDocument", "visible")
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
        $index = 1;
        foreach ($documents as $document) {
            //====================================================================//
            // READ Fields
            switch ($fieldId) {
                //====================================================================//
                // DOCUMENTS INFORMATION
                //====================================================================//
                case 'document':
                case 'position':
                case 'visible':
                case 'code':
                case 'label':
                    $value = $document[$fieldId] ?? null;

                    break;
                case 'label_with_skus':
                    if (!empty($document["skus"])) {
                        $skus = sprintf("[%s]", implode(", ", $document["skus"]));
                    }
                    $value = sprintf("%s %s", $skus ?? null, $document['label'] ?? null);

                    break;
                case 'skus':
                    $value = InlineHelper::fromArray($document["skus"]);

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
     * @return array<string, DocumentArray>
     */
    private function getAllDocuments(AkeneoProduct $product): array
    {
        //====================================================================//
        // Cache Already Loaded
        if (isset(self::$documentsCache)) {
            return self::$documentsCache;
        }
        //====================================================================//
        // Walk on Documents Attributes
        $attributes = $this->attr->findByType(AttributeTypes::FILE);
        $index = 0;
        foreach ($attributes as $attribute) {
            //====================================================================//
            // Read Attribute for Current Product
            $this->getAddDocumentToCache($product, $attribute, $index, true);
            //====================================================================//
            // Walk on Other Product Variants
            foreach ($this->variants->getVariantsList($product, true) as $variant) {
                //====================================================================//
                // Skip Current Product
                if ($variant->getUuid() === $product->getUuid()) {
                    continue;
                }
                //====================================================================//
                // Read Attribute for Product Variant
                $this->getAddDocumentToCache($variant, $attribute, $index, false);
            }
            $index++;
        }

        return self::$documentsCache ?? array();
    }

    /**
     * Add Product Attribute to Documents Cache
     *
     * @throws Exception
     */
    private function getAddDocumentToCache(
        AkeneoProduct $product,
        AttributeInterface $attribute,
        int $position,
        bool $visible
    ): void {
        //====================================================================//
        // Fetch Product Document Attribute
        $splDoc = $this->attr->get($product, $attribute->getCode());
        if (!$document = $splDoc[$attribute->getCode()] ?? null) {
            return;
        }
        if ((!$checksum = $document["md5"] ?? null) || !is_string($checksum)) {
            return;
        }
        //====================================================================//
        // Get Document Label Translation
        $defaultLocale = $this->locales->getDefault();
        $label = null;
        if ($defaultLocale) {
            /** @var AttributeTranslation $translation */
            $translation = $attribute->getTranslation($defaultLocale);
            $label = $translation->getLabel();
        }
        //====================================================================//
        // Populate Documents Cache
        $key = sprintf("%s-%s", $attribute->getCode(), $checksum);
        self::$documentsCache[$key] ??= array(
            "code" => $attribute->getCode(),
            "label" => $label,
            "position" => abs($position),
            "visible" => false,
            "document" => $document,
            "checksum" => $checksum,
            "skus" => array(),
        );
        self::$documentsCache[$key]["skus"][] = $product->getReference();
        if ($visible) {
            self::$documentsCache[$key]["visible"] = true;
        }
    }
}
