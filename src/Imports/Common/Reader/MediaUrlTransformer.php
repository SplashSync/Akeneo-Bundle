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

namespace Splash\Akeneo\Imports\Common\Reader;

use Akeneo\Tool\Component\Connector\Reader\File\MediaPathTransformer as BaseMediaPathTransformer;

/**
 * Media Transformer for Downloading Files from Urls
 */
class MediaUrlTransformer extends BaseMediaPathTransformer
{
    /**
     * {@inheritDoc}
     */
    public function transform(array $attributeValues, $filePath)
    {
        $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();

        foreach ($attributeValues as $code => $values) {
            if (in_array($code, $mediaAttributes, true)) {
                foreach ($values as $index => $value) {
                    if (isset($value['data'])) {
                        $dataFilePath = $value['data'];
                        $attributeValues[$code][$index]['data'] = $dataFilePath
                            ? $this->getPath($filePath, $dataFilePath)
                            : null
                        ;
                    }
                }
            }
        }

        return $attributeValues;
    }

    /**
     * @param string $filePath
     * @param string $data
     *
     * @return null|string
     */
    protected function getPath(string $filePath, string $data): ?string
    {
        if (filter_var($data, FILTER_VALIDATE_URL)) {
            return $this->download($data);
        }

        return sprintf('%s%s%s', $filePath, DIRECTORY_SEPARATOR, $data);
    }

    /**
     * @param string $url
     *
     * @return null|string
     */
    protected function download(string $url): ?string
    {
        //====================================================================//
        // Decode Media Url
        $parsedUrl = parse_url($url);
        if (!$parsedUrl) {
            return null;
        }
        //====================================================================//
        // Prepare Download Parameters
        $dir = sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, $parsedUrl['host']);
        $filename = sprintf('%s.%s', sha1($url), pathinfo($parsedUrl['path'], PATHINFO_EXTENSION));
        $path = sprintf('%s%s%s', $dir, DIRECTORY_SEPARATOR, $filename);
        //====================================================================//
        // Download File
        try {
            $content = file_get_contents($url);

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            file_put_contents($path, $content);
        } catch (\Exception $e) {
            return null;
        }

        return $path;
    }
}
