<?php

namespace Splash\Akeneo\Objects\Product;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

trait DatesTrait
{
    protected function exportDates(ProductInterface $Object, AttributeInterface $Attribute)
    {
        if ($this->getAttributeValue($Object, $Attribute) instanceof \DateTime) {
            return $this->exportDate($this->getAttributeValue($Object, $Attribute));
        }
        
        return null;
    }
}
