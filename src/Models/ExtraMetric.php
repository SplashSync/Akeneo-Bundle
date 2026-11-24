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

use Splash\Akeneo\Dictionary\ExtraMetricFormats;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extra Metric Virtual Field Definition, as Configured by User
 *
 * A Metric Attribute, Converted to a Target Unit & Exposed in a Given Format.
 */
class ExtraMetric
{
    /**
     * Allowed Characters for Field Names
     *
     * @var string
     */
    const NAME_PATTERN = "/^[A-Za-z0-9_]+$/";

    /**
     * Class Constructor
     *
     * @param string $name      Field Name, as Configured by User
     * @param string $attribute Source Metric Attribute Code
     * @param string $unit      Target Unit Code
     * @param string $format    Output Format
     */
    public function __construct(
        public readonly string $name,
        public readonly string $attribute,
        public readonly string $unit,
        public readonly string $format,
    ) {
    }

    /**
     * Build Extra Metric Definition from Configuration Entry
     *
     * Field name is taken from "name" if defined, else from entry key.
     *
     * @param int|string $key   Configuration Entry Key
     * @param mixed      $entry Configuration Entry
     *
     * @throws ExceptionInterface
     *
     * @return self
     */
    public static function fromConfig(int|string $key, mixed $entry): self
    {
        $resolver = (new OptionsResolver())
            ->setDefaults(array(
                "name" => is_string($key) ? $key : null,
                "format" => ExtraMetricFormats::DOUBLE,
            ))
            ->setRequired(array("name", "attribute", "unit"))
            ->setAllowedTypes("name", "string")
            ->setAllowedTypes("attribute", "string")
            ->setAllowedTypes("unit", "string")
            ->setAllowedValues("name", static fn (string $name): bool => (bool) preg_match(self::NAME_PATTERN, $name))
            ->setAllowedValues("format", ExtraMetricFormats::ALL)
        ;
        /** @var array{name: string, attribute: string, unit: string, format: string} $options */
        $options = $resolver->resolve(is_array($entry) ? $entry : array());

        return new self($options["name"], $options["attribute"], $options["unit"], $options["format"]);
    }

    /**
     * Get Splash Field Identifier
     *
     * @return string
     */
    public function getFieldId(): string
    {
        return TypesConverter::EXTRA_METRIC.$this->name;
    }

    /**
     * Get Target Unit Code in Pascal Case (i.e. SQUARE_METER => SquareMeter)
     *
     * @return string
     */
    public function getUnitPascalCase(): string
    {
        return str_replace("_", "", ucwords(strtolower($this->unit), "_"));
    }

    /**
     * Get Splash Field Type
     *
     * @return string
     */
    public function getSplashType(): string
    {
        return ExtraMetricFormats::SPLASH_TYPES[$this->format];
    }

    /**
     * Format a Converted Value
     *
     * @param float $value Value in Target Unit
     *
     * @return float|int
     */
    public function format(float $value): float|int
    {
        return (ExtraMetricFormats::INT == $this->format) ? (int) round($value) : $value;
    }
}
