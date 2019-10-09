<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Akeneo\Models;

use Pim\Component\Catalog\AttributeTypes;

/**
 * Map Akeneo Attributes Type to Splash Field Type
 */
class TypesConverter
{
    /**
     * List of Core Akeneo Attributes Types
     *
     * @var array
     */
    const CORE = array("sku", "enabled");    
    
    /**
     * List of Known Akeneo Attributes Types
     *
     * @var array
     */
    const TYPES = array(
        AttributeTypes::BOOLEAN => SPL_T_BOOL,
        AttributeTypes::DATE => SPL_T_DATE,
        AttributeTypes::NUMBER => SPL_T_INT,
        AttributeTypes::METRIC => SPL_T_DOUBLE,
//        AttributeTypes::FILE => SPL_T_FILE,
//        AttributeTypes::IMAGE => SPL_T_IMG,
        AttributeTypes::PRICE_COLLECTION => SPL_T_PRICE,
        AttributeTypes::IDENTIFIER => SPL_T_VARCHAR,
        AttributeTypes::OPTION_SIMPLE_SELECT => SPL_T_VARCHAR,
        AttributeTypes::TEXT => SPL_T_VARCHAR,
        AttributeTypes::TEXTAREA => SPL_T_TEXT,
    );

    /**
     * List of Read Only Akeneo Attributes Types
     *
     * @var array
     */
    const READONLY = array(
        AttributeTypes::FILE,
        AttributeTypes::IMAGE,
    );
    

    /**
     * List of Select Akeneo Attributes Types
     *
     * @var array
     */
    const SELECT = array(
        AttributeTypes::OPTION_SIMPLE_SELECT,
    );    
    
    /**
     * Check if Attribute type Code is Known
     *
     *  @param  string $attrType       Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isKnown(string $attrType): bool
    {
        return isset(self::TYPES[$attrType]);
    }
    
    /**
     * Check if Attribute type Code is a Core Type
     *
     *  @param  string $attrType       Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isCore(string $attrType): bool
    {
        return self::isKnown($attrType) && in_array($attrType, self::CORE, true);
    }    
    
    /**
     * Check if Attribute type Code is Read Only Type
     *
     *  @param  string $attrType       Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isReadOnly(string $attrType): bool
    {
        return self::isKnown($attrType) && in_array($attrType, self::READONLY, true);
    }    
    
    /**
     * Check if Attribute type Code is Select Type
     *
     *  @param  string $attrType       Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isSelect(string $attrType): bool
    {
        return self::isKnown($attrType) && in_array($attrType, self::SELECT, true);
    }    
    
    /**
     *  Convert Akeneo Attribute Type to Splash Field Type
     * 
     *  @param  string $attrType       Akeneo Attribute Type
     * 
     *  @return         string|null
     */    
    public static function toSplash(string $attrType): ?string
    {
        if (isset(self::TYPES[$attrType])) {
            return self::TYPES[$attrType];
        }
        
        return null;
    }      
    
}
