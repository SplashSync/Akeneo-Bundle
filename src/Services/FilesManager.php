<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Akeneo\Services;

use Akeneo\Tool\Bundle\FileStorageBundle\Doctrine\ORM\Repository\FileInfoRepository;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use ArrayObject;
use Exception;
use League\Flysystem\MountManager;
use Splash\Client\Splash as Splash;
use Splash\Models\FileProviderInterface;
use Splash\Models\Objects\ImagesTrait as SplashImages;
use SplFileInfo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface as Router;

/**
 * Splash Akeneo Files Management
 */
class FilesManager implements FileProviderInterface
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
     * @var FileInfoRepository
     */
    private $repository;

    /**
     * @var RemoverInterface
     */
    private $remover;

    /**
     * @var MountManager
     */
    private $mount;

    /**
     * Service Constructor
     *
     * @param Router       $router
     * @param FileStorer   $storer
     * @param MountManager $mountManager
     */
    public function __construct(
        Router $router,
        FileStorer $storer,
        RemoverInterface $remover,
        FileInfoRepository $repository,
        MountManager $mountManager
    ) {
        $this->router = $router;
        $this->storer = $storer;
        $this->repository = $repository;
        $this->remover = $remover;
        $this->mount = $mountManager;
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
        try {
            $fileSystem = $this->mount->getFilesystem($file->getStorage());
            //====================================================================//
            // Ensure Image Exists in File System
            if (empty($file->getKey()) || !$fileSystem->has($file->getKey())) {
                return null;
            }
            //====================================================================//
            // Read Raw File from File System
            $rawFile = $fileSystem->read($file->getKey());
            if (empty($rawFile)) {
                return null;
            }

            return array(
                "name" => $file->getOriginalFilename(),
                "filename" => $file->getOriginalFilename(),
                "path" => $file->getKey(),
                "url" => $this->router->generate(
                    "pim_enrich_media_show",
                    array( "filename" => urlencode($file->getKey()), "filter" => "preview" ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                "width" => "0",
                "height" => "0",
                "md5" => md5($rawFile),
                "size" => strlen($rawFile),
            );
        } catch (Exception $exception) {
            Splash::log()->report($exception);

            return null;
        }
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
        $writeFile = Splash::file()->writeFile(
            sys_get_temp_dir()."/",
            $splfile["filename"],
            $splfile["md5"],
            $rawFile["raw"]
        );
        if (!$writeFile) {
            return null;
        }
        //====================================================================//
        // Add File to Akeneo Storage
        try {
            $fullPath = sys_get_temp_dir()."/".$splfile["filename"];
            $newFile = $this->storer->store(new SplFileInfo($fullPath), "catalogStorage", true);
        } catch (Exception $exception) {
            Splash::log()->report($exception);

            return null;
        }

        return ($newFile instanceof FileInfo) ? $newFile->getKey() : null;
    }

    /**
     * Update Contents of a File
     *
     * @param FileInfo $file
     *
     * @return bool
     */
    public function delete(FileInfo $file): bool
    {
        try {
            //====================================================================//
            // Remove from Database
            $fileInfos = $this->repository->findOneByIdentifier($file->getKey());
            if ($fileInfos) {
                $this->remover->remove($fileInfos);
            }
            //====================================================================//
            // Remove from File System
            $fileSystem = $this->mount->getFilesystem("catalogStorage");
            if (!empty($file->getKey()) && $fileSystem->has($file->getKey())) {
                $fileSystem->delete($file->getKey());
            }
        } catch (Exception $exception) {
            Splash::log()->report($exception);

            return false;
        }

        return true;
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

        try {
            $fileSystem = $this->mount->getFilesystem("catalogStorage");
            //====================================================================//
            // Ensure File Exists in File System
            if (!$fileSystem->has($current->getKey())) {
                return false;
            }
            //====================================================================//
            // Ensure Md5 are Similar
            $rawFile = $fileSystem->read($current->getKey());
            if (empty($rawFile) || (md5($rawFile) != $splashfile["md5"])) {
                return false;
            }

            return true;
        } catch (Exception $exception) {
            Splash::log()->report($exception);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function hasFile($file = null, $md5 = null)
    {
        try {
            $fileSystem = $this->mount->getFilesystem("catalogStorage");
            //====================================================================//
            // Ensure File Exists in File System
            if (empty($file) || !$fileSystem->has($file)) {
                return false;
            }
            //====================================================================//
            // Ensure Md5 are Similar
            $rawFile = $fileSystem->read($file);
            if (empty($rawFile) || (md5($rawFile) != $md5)) {
                return false;
            }

            return true;
        } catch (Exception $exception) {
            Splash::log()->report($exception);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function readFile($file = null, $md5 = null)
    {
        try {
            $fileSystem = $this->mount->getFilesystem("catalogStorage");
            //====================================================================//
            // Ensure Image Exists in File System
            if (empty($file) || !$fileSystem->has($file)) {
                return false;
            }
            //====================================================================//
            // Read Raw File from File System
            $rawFile = $fileSystem->read($file);
            if (empty($rawFile)) {
                return false;
            }

            return array(
                "name" => basename($file),
                "filename" => basename($file),
                "path" => $file,
                "width" => "0",
                "height" => "0",
                "md5" => md5($rawFile),
                "raw" => base64_encode($rawFile),
                "size" => $fileSystem->getSize($file),
            );
        } catch (Exception $exception) {
            Splash::log()->report($exception);

            return false;
        }
    }
}
