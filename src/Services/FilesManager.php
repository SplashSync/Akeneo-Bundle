<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Akeneo\Services;

use Akeneo\Component\FileStorage\File\FileStorer;
use Akeneo\Component\FileStorage\Model\FileInfo;
use ArrayObject;
use Exception;
use Splash\Client\Splash as Splash;
use Splash\Models\Objects\ImagesTrait as SplashImages;
use SplFileInfo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface as Router;

/**
 * Splash Akeneo Files Management
 */
class FilesManager
{
    use SplashImages;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var FileStorer
     */
    private $storer;

    /**
     * @var string
     */
    private $storageDir;

    /**
     * Service Constructor
     *
     * @param Router     $router
     * @param FileStorer $storer
     * @param string     $storageDir
     */
    public function __construct(Router $router, FileStorer $storer, string $storageDir)
    {
        $this->router = $router;
        $this->storer = $storer;
        $this->storageDir = $storageDir;
    }

    /**
     * Convert a Akeneo File Info to Splash Image Array
     *
     * @param FileInfo $file
     *
     * @return array
     */
    public function getSplashImage(FileInfo $file): ?array
    {
        if (empty($file->getKey())) {
            return null;
        }

        $image = self::Images()->encode(
            $file->getOriginalFilename(),
            $file->getKey(),
            $this->storageDir."/",
            $this->router->generate(
                "pim_enrich_media_show",
                array( "filename" => urlencode($file->getKey()), "filter" => "preview" ),
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );

        return $image ? $image : null;
    }

    /**
     * Add a File to Storage form Splash Server
     *
     * @param array|ArrayObject $splfile
     *
     * @return null|string
     */
    public function add($splfile): ?string
    {
        //====================================================================//
        // Verify New Splash File is Valid
        if (!$this->isValid($splfile)) {
            return null;
        }
        if (!isset($splfile["path"]) || !isset($splfile["md5"]) || !isset($splfile["filename"])) {
            return null;
        }
        //====================================================================//
        // Read Raw File from Splash
        $rawFile = Splash::file()->getFile($splfile["path"], $splfile["md5"]);
        if (!$rawFile) {
            return null;
        }
        //====================================================================//
        // Write File to Temp Directory
        $writeFile = Splash::file()->writeFile(sys_get_temp_dir()."/", $splfile["filename"], $splfile["md5"], $rawFile["raw"]);
        if (!$writeFile) {
            return null;
        }
        //====================================================================//
        // Add File to Akeneo Storage
        try {
            $fullPath = sys_get_temp_dir()."/".$splfile["filename"];
            $newFile = $this->storer->store(new SplFileInfo($fullPath), "catalogStorage", true);
        } catch (Exception $e) {
            Splash::Log()->Err($e->getMessage());
            Splash::Log()->Err($e->getTraceAsString());

            return null;
        }

        return ($newFile instanceof FileInfo) ? ($this->storageDir."/".$newFile->getKey()) : null;
    }

    /**
     * Update Contents of a File
     *
     * @param FileInfo $current
     *
     * @return bool
     */
    public function delete(FileInfo $current): ?bool
    {
        $fullPath = $this->getFullPath($current);
        //====================================================================//
        // Delete Raw File from Filesystem
        return Splash::file()->deleteFile($fullPath, (string) md5_file($fullPath));
    }

    /**
     * Verify Input is Valid
     *
     * @param array|ArrayObject $splashfile
     *
     * @return bool
     */
    public function isValid($splashfile): ?bool
    {
        //====================================================================//
        // Verify New Splash File is Valid
        if (!isset($splashfile["path"]) || !isset($splashfile["md5"]) || !isset($splashfile["filename"])) {
            return false;
        }
        if (empty($splashfile["path"]) || empty($splashfile["md5"]) || empty($splashfile["filename"])) {
            return false;
        }

        return true;
    }

    /**
     * Check if Files are Similar
     *
     * @param FileInfo          $current
     * @param array|ArrayObject $splashfile
     *
     * @return bool
     */
    public function isSimilar(FileInfo $current, $splashfile): bool
    {
        //====================================================================//
        // Verify New Splash File is Valid
        if (!$this->isValid($splashfile) || !isset($splashfile["md5"])) {
            return false;
        }
        //====================================================================//
        // Verify CheckSum of Current File
        $currentFilePath = $this->getFullPath($current);
        if (!is_file($currentFilePath)) {
            return false;
        }

        return (md5_file($currentFilePath) == $splashfile["md5"]);
    }

    /**
     * @param FileInfo $current
     *
     * @return string
     */
    private function getFullPath(FileInfo $current): string
    {
        return $this->storageDir."/".$current->getKey();
    }
}
