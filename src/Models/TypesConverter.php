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

namespace Splash\Akeneo\Models;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as Attribute;

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
     * Bool as String Prefix
     *
     * @var string
     */
    const BOOL2STRING = "b2s_";

    /**
     * Metrics as String Prefix
     *
     * @var string
     */
    const METRIC2STRING = "m2s_";

    /**
     * Select Translations Prefix
     *
     * @var string
     */
    const SELECT2TRANS = "s2t_";

    /**
     * Multi-Select Translations Prefix
     *
     * @var string
     */
    const MULTI2TRANS = "m2t_";

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
        AttributeTypes::IMAGE => SPL_T_IMG,
        AttributeTypes::PRICE_COLLECTION => SPL_T_PRICE,
        AttributeTypes::IDENTIFIER => SPL_T_VARCHAR,
        AttributeTypes::OPTION_SIMPLE_SELECT => SPL_T_VARCHAR,
        AttributeTypes::TEXT => SPL_T_VARCHAR,
        AttributeTypes::TEXTAREA => SPL_T_TEXT,
        AttributeTypes::OPTION_MULTI_SELECT => SPL_T_INLINE,
        AttributeTypes::FILE => SPL_T_FILE,
    );

    /**
     * List of Read Only Akeneo Attributes Types
     *
     * @var array
     */
    const READONLY = array(
        AttributeTypes::OPTION_MULTI_SELECT,
        AttributeTypes::FILE,
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
     * List of Select Akeneo Attributes Types
     *
     * @var array
     */
    const NUMBER = array(
        AttributeTypes::NUMBER,
    );

    /**
     * Check if Attribute type Code is Known
     *
     * @param string $attrType Akeneo Attribute Type
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
     * @param string $attrType Akeneo Attribute Type
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
     * @param string $attrType Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isReadOnly(string $attrType): bool
    {
        return self::isKnown($attrType) && in_array($attrType, self::READONLY, true);
    }

    /**
     * Check if Attribute type Code is Primary Key Type
     *
     * @param string $attrType Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isPrimary(string $attrType): bool
    {
        return self::isKnown($attrType) && (AttributeTypes::IDENTIFIER == $attrType);
    }

    /**
     * Check if Attribute type Code is Select Type
     *
     * @param string $attrType Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isSelect(string $attrType): bool
    {
        return self::isKnown($attrType) && in_array($attrType, self::SELECT, true);
    }

    /**
     * Check if Attribute type Code is Select Type
     *
     * @param string $attrType Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isMultiSelect(string $attrType): bool
    {
        return self::isKnown($attrType) && (AttributeTypes::OPTION_MULTI_SELECT == $attrType);
    }

    /**
     * Check if Attribute type Code is a Number Type
     *
     * @param string $attrType Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isNumber(string $attrType): bool
    {
        return self::isKnown($attrType) && in_array($attrType, self::NUMBER, true);
    }

    /**
     * Check if Attribute type Code is a Metric Type
     *
     * @param string $attrType Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isMetric(string $attrType): bool
    {
        return self::isKnown($attrType) && (AttributeTypes::METRIC == $attrType);
    }

    /**
     * Check if Attribute type Code is a Boolean Type
     *
     * @param string $attrType Akeneo Attribute Type
     *
     * @return bool
     */
    public static function isBool(string $attrType): bool
    {
        return self::isKnown($attrType) && (AttributeTypes::BOOLEAN == $attrType);
    }

    /**
     * Convert Akeneo Attribute Type to Splash Field Type
     *
     * @param Attribute $attribute   Akeneo Attribute Type
     * @param bool      $catalogMode Akeneo Catalog Mode
     *
     * @return null|string
     */
    public static function toSplash(Attribute $attribute, bool $catalogMode): ?string
    {
        $attrType = $attribute->getType();
        //====================================================================//
        // Ensure Attribute Type is Compatible with Splash
        if (!isset(self::TYPES[$attrType])) {
            return null;
        }
        $splashType = self::TYPES[$attrType];
        //====================================================================//
        // Detect Mapping Exceptions (INT >> DOUBLE)
        if (self::isNumber($attrType) && $attribute->isDecimalsAllowed()) {
            $splashType = SPL_T_DOUBLE;
        }
        //====================================================================//
        // Detect Mapping Exceptions (CATALOG MODE)
        if (self::isNumber($attrType) && $catalogMode) {
            $splashType = SPL_T_VARCHAR;
        }

        return $splashType;
    }

    //====================================================================//
    // Virtual Fields Names Detection
    //====================================================================//

    /**
     * Detect & Decode Virtual FieldName
     *
     * @param string $fieldName Complete Field Name
     *
     * @return null|string Base Field Name or Null
     */
    public static function isVirtual($fieldName): ?string
    {
        //====================================================================//
        // Bool to String
        $boolToString = self::isBoolToString($fieldName);
        if ($boolToString) {
            return $boolToString;
        }

        //====================================================================//
        // Metric to String
        $metricToString = self::isMetricToString($fieldName);
        if ($metricToString) {
            return $metricToString;
        }

        //====================================================================//
        // Select to Translation
        $selectToTrans = self::isSelectToTrans($fieldName);
        if ($selectToTrans) {
            return $selectToTrans;
        }

        //====================================================================//
        // Multi-Select to Translation
        $multiToTrans = self::isMultiToTrans($fieldName);
        if ($multiToTrans) {
            return $multiToTrans;
        }

        return null;
    }

    //====================================================================//
    // Bool to String Detection
    //====================================================================//

    /**
     * Detect & Decode Bool to String FieldName
     *
     * @param string $fieldName Complete Field Name
     *
     * @return null|string Base Field Name or Null
     */
    public static function isBoolToString($fieldName): ?string
    {
        //====================================================================//
        // Check if Prefix is in FieldName
        if (0 !== strpos($fieldName, self::BOOL2STRING)) {
            return null;
        }

        return substr($fieldName, strlen(self::BOOL2STRING));
    }

    //====================================================================//
    // Metric to String Detection
    //====================================================================//

    /**
     * Detect & Decode Metrics to String FieldName
     *
     * @param string $fieldName Complete Field Name
     *
     * @return null|string Base Field Name or Null
     */
    public static function isMetricToString($fieldName): ?string
    {
        //====================================================================//
        // Check if Prefix is in FieldName
        if (0 !== strpos($fieldName, self::METRIC2STRING)) {
            return null;
        }

        return substr($fieldName, strlen(self::METRIC2STRING));
    }

    //====================================================================//
    // Select to Translation Detection
    //====================================================================//

    /**
     * Detect & Decode Select to Translation FieldName
     *
     * @param string $fieldName Complete Field Name
     *
     * @return null|string Base Field Name or Null
     */
    public static function isSelectToTrans($fieldName): ?string
    {
        //====================================================================//
        // Check if Prefix is in FieldName
        if (0 !== strpos($fieldName, self::SELECT2TRANS)) {
            return null;
        }

        return substr($fieldName, strlen(self::SELECT2TRANS));
    }

    /**
     * Detect & Decode Multi-Select to Translation FieldName
     *
     * @param string $fieldName Complete Field Name
     *
     * @return null|string Base Field Name or Null
     */
    public static function isMultiToTrans($fieldName): ?string
    {
        //====================================================================//
        // Check if Prefix is in FieldName
        if (0 !== strpos($fieldName, self::MULTI2TRANS)) {
            return null;
        }

        return substr($fieldName, strlen(self::MULTI2TRANS));
    }
}
