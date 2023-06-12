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

namespace Splash\Akeneo\Imports\Common\Converter;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Exception;
use Symfony\Component\Yaml\Yaml;

/**
 * Convert Read Items using a Yaml Map
 */
class MappedArrayConverter implements ArrayConverterInterface
{
    /**
     * @var array
     */
    private array $map;

    /**
     * @param string $path
     *
     * @throws Exception
     */
    public function __construct(string $path)
    {
        //====================================================================//
        // Import Fields Mapping
        if (!is_file($path)) {
            throw new Exception("Invalid mapping file:".$path);
        }
        /** @var null|array $map */
        $map = Yaml::parseFile($path);
        if (empty($map)) {
            throw new Exception("Empty mapping file:".$path);
        }
        $this->map = $map;
    }

    /**
     * {@inheritDoc}
     */
    public function convert(array $item, array $options = array()): array
    {
        $result = array();
        //====================================================================//
        // Walk on Fields Mapping
        foreach ($this->map as $field => $column) {
            if (!empty($item[$column])) {
                $result[$field] = $item[$column];
            }
        }

        return $result;
    }
}
