<?php

namespace Splash\Akeneo\Objects\Core;

use Splash\Bundle\Annotation as SPL;

trait ObjectMetadataTrait
{
    
    //====================================================================//
    // OBJECT METADATA
    //====================================================================//
    
    /**
     * @SPL\Field(
     *          id      =   "created",
     *          type    =   "date",
     *          name    =   "Creation Date",
     *          itemtype=   "http://schema.org/DataFeedItem", itemprop="dateCreated",
     *          inlist  =   false,
     *          write   =   false,
     * )
     */
    protected $created;
    
    /**
     * @SPL\Field(
     *          id      =   "updated",
     *          type    =   "date",
     *          name    =   "Updated Date",
     *          itemtype=   "http://schema.org/DataFeedItem", itemprop="dateModified",
     *          inlist  =   true,
     *          write   =   false,
     * )
     */
    protected $modified;
}
