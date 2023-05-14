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

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface as Repository;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Splash Languages Manager - Akeneo Languages Management
 */
class LocalesManager
{
    /**
     * Fallback Language
     *
     * @var string
     */
    const FALLBACK_LOCALE = "en_US";

    /**
     * Default Language Code
     *
     * @var string
     */
    private string $default = self::FALLBACK_LOCALE;

    /**
     * List of All Available Languages Codes
     *
     * @var null|array
     */
    private ?array $locales;

    /**
     * @var Repository
     */
    private Repository $repository;

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Service Constructor
     *
     * @param Repository          $repository
     * @param TranslatorInterface $translator
     */
    public function __construct(Repository $repository, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    /**
     * Get Default Local Language ISO Code
     *
     * @param string|null $locale
     *
     * @return self
     */
    public function setDefault(string $locale = null): self
    {
        if (!empty($locale)) {
            $this->default = $locale;
        }

        return $this;
    }

    /**
     * Get Default Local Language ISO Code
     *
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * Check if is Default Local Language
     *
     * @param string $isoCode language ISO Code (i.e en_US | fr_FR)
     *
     * @return bool
     */
    public function isDefault(string $isoCode): bool
    {
        return ($isoCode == $this->getDefault());
    }

    /**
     * Get All Available Languages
     *
     * @return array
     */
    public function getAll(): array
    {
        //====================================================================//
        // Load From Cache
        if (!isset($this->locales)) {
            $this->locales = $this->repository->getActivatedLocaleCodes();
        }

        return $this->locales;
    }

    /**
     * Decode Multilang FieldName with ISO Code
     *
     * @param string $fieldName Complete Field Name
     * @param string $isoCode   Language Code in Splash Format
     *
     * @return null|string Base Field Name or Null
     */
    public function decode(string $fieldName, string $isoCode): ?string
    {
        //====================================================================//
        // Default Language => No code in FieldName
        if ($this->isDefault($isoCode)) {
            return $fieldName;
        }
        //====================================================================//
        // Other Languages => Check if Code is in FieldName
        if (false === strpos($fieldName, $isoCode)) {
            return null;
        }

        return substr($fieldName, 0, strlen($fieldName) - strlen($isoCode) - 1);
    }

    /**
     * Translates the given message.
     *
     * @param string $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @return string The translated string
     */
    public function trans(string $id, array $parameters = array(), string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }
}
