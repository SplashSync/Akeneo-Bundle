<?php

declare(strict_types=1);

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

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * PIM Kernel
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @return iterable
     */
    public function registerBundles(): iterable
    {
        /** @var array $bundles */
        $bundles = require $this->getProjectDir().'/vendor/akeneo/pim-community-dev/config/bundles.php';
        $bundles += require $this->getProjectDir().'/config/bundles.php';

        foreach ($bundles as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    /**
     * @return string
     */
    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/logs';
    }

    /**
     * @param ContainerBuilder $container
     * @param LoaderInterface  $loader
     *
     * @throws Exception
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);

        $ceConfDir = $this->getProjectDir().'/vendor/akeneo/pim-community-dev/config';
        $projectConfDir = $this->getProjectDir().'/config';

        $this->loadPackagesConfigurationExceptSecurity($loader, $ceConfDir, $this->environment);
        $this->loadPackagesConfiguration($loader, $projectConfDir, $this->environment);

        $this->loadContainerConfiguration($loader, $ceConfDir, $this->environment);
        $this->loadContainerConfiguration($loader, $projectConfDir, $this->environment);
    }

    /**
     * @param RouteCollectionBuilder $routes
     *
     * @throws LoaderLoadException
     */
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $this->loadRoutesConfiguration(
            $routes,
            $this->getProjectDir().'/vendor/akeneo/pim-community-dev/config',
            $this->environment
        );
        $this->loadRoutesConfiguration(
            $routes,
            $this->getProjectDir().'/config',
            $this->environment
        );
    }

    /**
     * @param RouteCollectionBuilder $routes
     * @param string                 $confDir
     * @param string                 $environment
     *
     * @throws LoaderLoadException
     */
    private function loadRoutesConfiguration(RouteCollectionBuilder $routes, string $confDir, string $environment): void
    {
        $routes->import($confDir.'/{routes}/'.$environment.'/**/*.yml', '/', 'glob');
        $routes->import($confDir.'/{routes}/*.yml', '/', 'glob');
    }

    /**
     * @param LoaderInterface $loader
     * @param string          $confDir
     * @param string          $environment
     *
     * @throws Exception
     */
    private function loadPackagesConfiguration(LoaderInterface $loader, string $confDir, string $environment): void
    {
        $loader->load($confDir.'/{packages}/*.yml', 'glob');
        $loader->load($confDir.'/{packages}/'.$environment.'/**/*.yml', 'glob');
    }

    /**
     * "security.yml" is the only configuration file that can not be override
     * Thus, we don't load it from the Community Edition.
     * We copied/pasted its content into Enterprise Edition and added what was missing.
     *
     * @param LoaderInterface $loader
     * @param string          $confDir
     * @param string          $environment
     *
     * @throws Exception
     */
    private function loadPackagesConfigurationExceptSecurity(
        LoaderInterface $loader,
        string $confDir,
        string $environment
    ): void {
        $files = array_merge(
            (array) glob($confDir.'/packages/*.yml'),
            (array) glob($confDir.'/packages/'.$environment.'/*.yml'),
            (array) glob($confDir.'/packages/'.$environment.'/**/*.yml')
        );

        $files = array_filter(
            $files,
            function ($file) {
                return 'security.yml' !== basename((string) $file);
            }
        );

        foreach ($files as $file) {
            $loader->load($file, 'yaml');
        }
    }

    /**
     * @param LoaderInterface $loader
     * @param string          $confDir
     * @param string          $environment
     *
     * @throws Exception
     */
    private function loadContainerConfiguration(LoaderInterface $loader, string $confDir, string $environment): void
    {
        $loader->load($confDir.'/{services}/*.yml', 'glob');
        $loader->load($confDir.'/{services}/'.$environment.'/**/*.yml', 'glob');
    }
}
