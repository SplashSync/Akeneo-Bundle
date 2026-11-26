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

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface as Category;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface as ChannelRepository;
use Doctrine\Common\Collections\Collection;
use Splash\Akeneo\Models\ExtraMetric;
use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Client\Splash;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

/**
 * Manage General Configuration for Splash Connector
 */
class Configuration
{
    /**
     * Default Separator for Multi-Select Values as String
     *
     * @var string
     */
    const DEFAULT_MULTISELECT_SEPARATOR = " | ";

    /**
     * Default Channel Code
     */
    private string $channel;

    /**
     * Default Locale Code
     *
     * @var string
     */
    private string $locale;

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
     * Filter Categories on Configured Channel Root Category
     *
     * @var bool
     */
    private bool $channelCategories = true;

    /**
     * List of Attributes Images parts of Image Gallery
     *
     * @var array[]
     *
     * @phpstan-var array{'code': string, 'label': null|string}[]
     */
    private array $imagesCodes = array();

    /**
     * Separator used to Join Multi-Select Values as String
     *
     * @var string
     */
    private string $multiSelectSeparator = self::DEFAULT_MULTISELECT_SEPARATOR;

    /**
     * Extra Metrics Virtual Fields, Indexed by Name
     *
     * @var array<string, ExtraMetric>
     */
    private array $extraMetrics = array();

    /**
     * Service Constructor
     */
    public function __construct(
        private readonly ChannelRepository $channelRepository,
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
        /** @var null|bool $learningMode */
        $learningMode = $object->getParameter("learning_mode", false);
        /** @var bool $catalogMode */
        $catalogMode = $object->getParameter("catalog_mode", false);
        /** @var null|bool $channelCategories */
        $channelCategories = $object->getParameter("channel_categories", true);
        /** @var array $imagesCodes */
        $imagesCodes = $object->getParameter("images", array());
        $multiSelectSeparator = $object->getParameter("multiselect_separator", self::DEFAULT_MULTISELECT_SEPARATOR);
        $extraMetrics = $object->getParameter("extra_metrics", array());

        //====================================================================//
        // Setup Configuration
        $this->channel = $channel;
        $this->locale = $locale;
        $this->currency = $currency;
        $this->learningMode = (bool) $learningMode;
        $this->catalogMode = (bool) $catalogMode;
        $this->channelCategories = (bool) ($channelCategories ?? true);
        $this->multiSelectSeparator = is_string($multiSelectSeparator) && ('' !== $multiSelectSeparator)
            ? $multiSelectSeparator
            : self::DEFAULT_MULTISELECT_SEPARATOR
        ;
        //====================================================================//
        // Setup Images Codes
        $this->setupImageCodes($imagesCodes);
        //====================================================================//
        // Setup Extra Metrics
        $this->setupExtraMetrics(is_array($extraMetrics) ? $extraMetrics : array());
        //====================================================================//
        // Reset Channel if Needed
        if (isset($this->channelObject) && ($channel != $this->channelObject->getCode())) {
            $this->channelObject = null;
        }

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
     * Get Connector Default Channel Locales
     *
     * @return null|Collection<LocaleInterface>
     */
    public function getChannelLocales(): ?Collection
    {
        return $this->getChannelObject()?->getLocales();
    }

    /**
     * Get Connector Default Locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Get Connector Default Category ID
     *
     * Null if Channel Categories Filtering is Disabled: all Categories Trees are Allowed.
     *
     * @return null|int
     */
    public function getRootCategoryId(): ?int
    {
        if (!$this->channelCategories) {
            return null;
        }

        return $this->getChannelObject()?->getCategory()->getId();
    }

    /**
     * Are Categories Filtered on Configured Channel Root Category
     *
     * @return bool
     */
    public function isChannelCategories(): bool
    {
        return $this->channelCategories;
    }

    /**
     * Check if Category is in Configured Channel Tree
     */
    public function isAllowedCategory(Category $category): bool
    {
        $rootId = $category->getRoot();
        $channelRootId = $this->getRootCategoryId();
        //====================================================================//
        // Safety Check =>> Category Root is Allowed
        if (!$rootId || !$channelRootId) {
            return true;
        }

        return ($rootId == $channelRootId);
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
     * @return array{'code': string, 'label': null|string}[]
     */
    public function getImagesCodes(): array
    {
        return $this->imagesCodes;
    }

    /**
     * Get Separator used to Join Multi-Select Values as String
     *
     * @return string
     */
    public function getMultiSelectSeparator(): string
    {
        return $this->multiSelectSeparator;
    }

    /**
     * Get List of Configured Extra Metrics Virtual Fields
     *
     * @return array<string, ExtraMetric>
     */
    public function getExtraMetrics(): array
    {
        return $this->extraMetrics;
    }

    /**
     * Get Configured Extra Metric by Name
     *
     * @param string $name Extra Metric Name
     *
     * @return null|ExtraMetric
     */
    public function getExtraMetric(string $name): ?ExtraMetric
    {
        return $this->extraMetrics[$name] ?? null;
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

    /**
     * Configure Gallery Images Codes
     *
     * @param array $imagesCodes
     *
     * @return void
     */
    private function setupImageCodes(array $imagesCodes): void
    {
        $this->imagesCodes = array();
        foreach ($imagesCodes as $imagesCode) {
            if (is_string($imagesCode)) {
                $this->imagesCodes[$imagesCode] = array(
                    'code' => $imagesCode,
                    'label' => null
                );
            }
            if (is_array($imagesCode) && is_string($imagesCode['code'] ?? null)) {
                $this->imagesCodes[$imagesCode['code']] = array(
                    'code' => $imagesCode['code'],
                    'label' => is_string($imagesCode['label'] ?? null)
                        ? $imagesCode['label']
                        : null
                );
            }
        }
    }

    /**
     * Configure Extra Metrics Virtual Fields
     *
     * Invalid or duplicated entries are skipped with a warning.
     *
     * @param array $extraMetrics Raw Configuration
     *
     * @return void
     */
    private function setupExtraMetrics(array $extraMetrics): void
    {
        $this->extraMetrics = array();
        foreach ($extraMetrics as $key => $entry) {
            try {
                $extraMetric = ExtraMetric::fromConfig($key, $entry);
            } catch (ExceptionInterface $ex) {
                Splash::log()->war(sprintf("Invalid extra metric configuration '%s': %s", $key, $ex->getMessage()));

                continue;
            }
            if (isset($this->extraMetrics[$extraMetric->name])) {
                Splash::log()->war(sprintf("Duplicated extra metric name: %s", $extraMetric->name));

                continue;
            }
            $this->extraMetrics[$extraMetric->name] = $extraMetric;
        }
    }
}
