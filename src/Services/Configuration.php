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

namespace Splash\Akeneo\Services;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface as ChannelRepository;
use Splash\Akeneo\Services\LocalesManager as Locales;
use Splash\Bundle\Models\AbstractStandaloneObject;

/**
 * Manage General Configuration for Splash Connector
 */
class Configuration
{
    /**
     * Default Scope Code
     *
     * @var string
     */
    private string $channel;

    /**
     * Default Channel
     *
     * @var null|ChannelInterface
     */
    private ?ChannelInterface $channelObject;

    /**
     * Default Currency Code
     *
     * @var string
     */
    private string $currency;

    /**
     * Work in Learning Mode
     *
     * @var bool
     */
    private bool $learningMode = false;

    /**
     * Work in Catalog Mode
     *
     * @var bool
     */
    private bool $catalogMode = false;

    /**
     * List of Attributes Images parts of Image Gallery
     *
     * @var string[]
     */
    private array $imagesCodes = array();

    /**
     * Service Constructor
     */
    public function __construct(
        private readonly ChannelRepository $channelRepository,
        private readonly Locales $locales
    ) {
    }

    /**
     * Setup Connector Configuration
     *
     * @param AbstractStandaloneObject $object
     *
     * @return self
     */
    public function setup(AbstractStandaloneObject $object): self
    {
        //====================================================================//
        // Collect Values from Object
        /** @var string $channel */
        $channel = $object->getParameter("channel", "ecommerce");
        /** @var string $currency */
        $currency = $object->getParameter("currency", "EUR");
        /** @var string $locale */
        $locale = $object->getParameter("locale", "en_US");
        /** @var bool $learningMode */
        $learningMode = $object->getParameter("learning_mode", false);
        /** @var bool $catalogMode */
        $catalogMode = $object->getParameter("catalog_mode", false);
        /** @var string[] $imagesCodes */
        $imagesCodes = $object->getParameter("images", array());

        //====================================================================//
        // Setup Configuration
        $this->channel = $channel;
        $this->currency = $currency;
        $this->learningMode = (bool) $learningMode;
        $this->catalogMode = (bool) $catalogMode;
        $this->imagesCodes = $imagesCodes;

        //====================================================================//
        // Reset Channel if Needed
        if (isset($this->channelObject) && ($channel != $this->channelObject->getCode())) {
            $this->channelObject = null;
        }

        //====================================================================//
        // Default Language
        $this->locales->setDefault($locale);

        return $this;
    }

    /**
     * Get Connector Default Channel
     *
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * Get Connector Default Channel ID
     *
     * @return null|int
     */
    public function getChannelId(): ?int
    {
        return $this->getChannelObject()?->getId();
    }

    /**
     * Get Connector Default Category ID
     *
     * @return null|int
     */
    public function getRootCategoryId(): ?int
    {
        return $this->getChannelObject()?->getCategory()->getId();
    }

    /**
     * Get Connector Default Currency
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Is in Learning Mode
     *
     * @return bool
     */
    public function isLearningMode(): bool
    {
        return $this->learningMode;
    }

    /**
     * Is in Learning Mode
     *
     * @return bool
     */
    public function isCatalogMode(): bool
    {
        return $this->catalogMode && !$this->isLearningMode();
    }

    /**
     * Get List of Attributes Used for Product Image Galley
     *
     * @return string[]
     */
    public function getImagesCodes(): array
    {
        return $this->imagesCodes;
    }

    /**
     * Get Connector Default Channel Object
     *
     * @return null|ChannelInterface
     */
    private function getChannelObject(): ?ChannelInterface
    {
        //====================================================================//
        // Channel Already Loaded
        if (isset($this->channelObject)) {
            return $this->channelObject;
        }

        //====================================================================//
        // Filter Categories on Default Channel
        $channelCode = $this->getChannel();
        if (empty($channelCode)) {
            return null;
        }
        //====================================================================//
        // Connect to Channels Repository
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        $this->channelObject = ($channel instanceof ChannelInterface) ? $channel : null;

        return $this->channelObject;
    }
}
