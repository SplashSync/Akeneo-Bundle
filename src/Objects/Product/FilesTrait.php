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

namespace Splash\Akeneo\Objects\Product;

/**
 * Product Files Access
 */
trait FilesTrait
{
    /**
     * {@inheritDoc}
     */
    public function hasFile(string $file, string $md5): bool
    {
        //====================================================================//
        //  Forward to Files Manager
        return $this->files->hasFile($file, $md5);
    }

    /**
     * {@inheritDoc}
     */
    public function readFile(string $file, string $md5): ?array
    {
        //====================================================================//
        //  Forward to Files Manager
        return $this->files->readFile($file, $md5);
    }
}
