<?php

namespace Splash\Akeneo\Services;

use Akeneo\Tool\Bundle\FileStorageBundle\Doctrine\ORM\Repository\FileInfoRepository;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;

use Exception;
use League\Flysystem\FilesystemException;
use League\Flysystem\MountManager;
use Splash\Client\Splash as Splash;
use Splash\Models\FileProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface as Router;

class DocumentsManager implements FileProviderInterface
{
    /**
     * Service Constructor
     */
    public function __construct(
        private readonly FileStorer         $storer,
        private readonly RemoverInterface   $remover,
        private readonly FileInfoRepository $repository,
        private readonly MountManager       $mount,
        private readonly Router             $router,
    )
    {
    }

    /**
     * Convert an Akeneo File Info to Splash Document Array
     *
     * @param FileInfo $file
     *
     * @return null|array
     */
    public function getSplashDocument(FileInfo $file): ?array
    {
        try {
            $path = $file->getStorage() . "://" . $file->getKey();
            //====================================================================//
            // Ensure Document Exists in File System
            if (empty($file->getKey()) || !$this->mount->has($path)) {
                return null;
            }
            //====================================================================//
            // Build File Info Array
            return array(
                "name" => $file->getOriginalFilename(),
                "filename" => $file->getOriginalFilename(),
                "path" => $path,
                "url" => $this->router->generate(
                    "pim_enrich_media_download",
                    array("path" => $file->getKey()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                "md5" => $file->getHash(),
                "size" => $file->getSize(),
            );
        } catch (Exception|FilesystemException $exception) {
            Splash::log()->report($exception);
            return null;
        }
    }

    public function add(array $splashFile): ?string
    {
        //====================================================================//
        // Verify New Splash File is Valid
        if (!$this->isValid($splashFile)) {
            return null;
        }
        if (!isset($splashFile["path"]) || !isset($splashFile["md5"]) || !isset($splashFile["filename"])) {
            return null;
        }
        //====================================================================//
        // Read Raw File from Splash
        $rawFile = Splash::file()->getFile($splashFile["file"] ?? $splashFile["path"], $splashFile["md5"]);
        if (!$rawFile) {
            return null;
        }
        //====================================================================//
        // Write File to Temp Directory
        $writeFile = Splash::file()->writeFile(
            sys_get_temp_dir() . "/",
            $splashFile["filename"],
            $splashFile["md5"],
            $rawFile["raw"]
        );
        if (!$writeFile) {
            return null;
        }
        //====================================================================//
        // Add File to Akeneo Storage
        try {
            $fullPath = sys_get_temp_dir() . "/" . $splashFile["filename"];
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
            $path = $file->getStorage() . "://" . $file->getKey();
            //====================================================================//
            // Remove from Database
            $fileInfos = $this->repository->findOneByIdentifier($file->getKey());
            if ($fileInfos) {
                $this->remover->remove($fileInfos);
            }
            //====================================================================//
            // Remove from File System
            if ($this->mount->has($path)) {
                $this->mount->delete($path);
            }
        } catch (Exception|FilesystemException $exception) {
            Splash::log()->report($exception);

            return false;
        }

        return true;
    }


    /**
     * Verify Input is Valid
     *
     * @param null|array $splashFile
     *
     * @return bool
     */
    public function isValid(?array $splashFile): ?bool
    {
        //====================================================================//
        // Verify New Splash File is Valid
        if (!is_array($splashFile)) {
            return false;
        }
        if (empty($splashFile["path"]) || empty($splashFile["md5"]) || empty($splashFile["filename"])) {
            return false;
        }

        return true;
    }

    /**
     * Check if Files are Similar
     *
     * @param FileInfo $current
     * @param array $splashFile
     *
     * @return bool
     */
    public function isSimilar(FileInfo $current, array $splashFile): bool
    {
        //====================================================================//
        // Verify New Splash File is Valid
        if (!$this->isValid($splashFile) || !isset($splashFile["md5"])) {
            return false;
        }

        try {
            $path = $current->getStorage() . "://" . $current->getKey();
            //====================================================================//
            // Ensure File Exists in File System
            if (!$this->mount->has($path)) {
                return false;
            }
            //====================================================================//
            // Ensure Md5 are Similar
            if ($this->mount->checksum($path) != $splashFile["md5"]) {
                return false;
            }

            return true;
        } catch (Exception|FilesystemException $exception) {
            Splash::log()->report($exception);

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function hasFile(string $file, string $md5): bool
    {
        try {
            //====================================================================//
            // Ensure Document Exists in File System
            if (empty($file) || !$this->mount->has($file)) {
                return false;
            }
            //====================================================================//
            // Ensure Md5 are Similar
            if ($this->mount->checksum($file) != $md5) {
                return false;
            }

            return true;
        } catch (Exception|FilesystemException $exception) {
            Splash::log()->report($exception);
            return false;

        }
    }

    /**
     * @inheritDoc
     */
    public function readFile(string $file, string $md5): ?array
    {
        try {
            //====================================================================//
            // Ensure Document Exists in File System
            if (empty($file) || !$this->mount->has($file)) {
                return null;
            }
            //====================================================================//
            // Read Raw File from File System
            $rawFile = $this->mount->read($file);
            if (empty($rawFile)) {
                return null;
            }

            return array(
                "name" => basename($file),
                "filename" => basename($file),
                "path" => $file,
                "md5" => md5($rawFile),
                "raw" => base64_encode($rawFile),
                "size" => $this->mount->fileSize($file),
            );
        } catch (Exception|FilesystemException $exception) {
            Splash::log()->report($exception);
            return null;
        }
    }
}
