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

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * PIM AppKernel
 */
class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = $this->registerProjectBundles();

        if (in_array($this->getEnvironment(), array('dev', 'test', 'behat'), true)) {
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
        }

        return array_merge(
            $this->getSymfonyBundles(),
            $this->getOroDependencies(),
            $this->getOroBundles(),
            $this->getPimDependenciesBundles(),
            $this->getPimBundles(),
            $bundles
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');

        if (is_file($file = $this->getRootDir().'/config/config_'.$this->getEnvironment().'_local.yml')) {
            $loader->load($file);
        }
    }

    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return __DIR__;
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return dirname(__DIR__)
            .DIRECTORY_SEPARATOR
            .'var'
            .DIRECTORY_SEPARATOR
            .'cache'
            .DIRECTORY_SEPARATOR
            .$this->getEnvironment();
    }

    /**
     * @return string
     */
    public function getLogDir(): string
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'logs';
    }

    /**
     * Registers your custom bundles
     *
     * @return array
     */
    protected function registerProjectBundles()
    {
        return array(
            new \Splash\Bundle\SplashBundle(),
            new \Splash\Console\ConsoleBundle(),
            new \Splash\Akeneo\SplashAkeneoBundle(),
        );
    }

    /**
     * Bundles coming from the PIM
     *
     * @return array
     */
    protected function getPimBundles()
    {
        return array(
            // BAP overriden bundles
            new Oro\Bundle\PimFilterBundle\PimFilterBundle(),
            new Oro\Bundle\PimDataGridBundle\PimDataGridBundle(),
            new Akeneo\UserManagement\Bundle\PimUserBundle(),

            // Channel bundles
            new Akeneo\Channel\Bundle\AkeneoChannelBundle(),

            // PIM bundles
            new Akeneo\Pim\Enrichment\Bundle\AkeneoPimEnrichmentBundle(),
            new Akeneo\Pim\Structure\Bundle\AkeneoPimStructureBundle(),

            // Platform bundles
            new Akeneo\Platform\Bundle\DashboardBundle\PimDashboardBundle(),
            new Akeneo\Platform\Bundle\AnalyticsBundle\PimAnalyticsBundle(),
            new Akeneo\Platform\Bundle\ImportExportBundle\PimImportExportBundle(),
            new Akeneo\Platform\Bundle\InstallerBundle\PimInstallerBundle(),
            new Akeneo\Platform\Bundle\NotificationBundle\PimNotificationBundle(),
            new Akeneo\Platform\Bundle\UIBundle\PimUIBundle(),
            new Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\PimCatalogVolumeMonitoringBundle(),
        );
    }

    /**
     * Bundles required by the PIM
     *
     * @return array
     */
    protected function getPimDependenciesBundles()
    {
        return array(
            new Akeneo\Tool\Bundle\ConnectorBundle\PimConnectorBundle(),
            new Akeneo\Tool\Bundle\ClassificationBundle\AkeneoClassificationBundle(),
            new Akeneo\Tool\Bundle\VersioningBundle\AkeneoVersioningBundle(),
            new Akeneo\Tool\Bundle\ElasticsearchBundle\AkeneoElasticsearchBundle(),
            new Akeneo\Tool\Bundle\BatchBundle\AkeneoBatchBundle(),
            new Akeneo\Tool\Bundle\BatchQueueBundle\AkeneoBatchQueueBundle(),
            new Akeneo\Tool\Bundle\BufferBundle\AkeneoBufferBundle(),
            new Akeneo\Tool\Bundle\FileStorageBundle\AkeneoFileStorageBundle(),
            new Akeneo\Tool\Bundle\MeasureBundle\AkeneoMeasureBundle(),
            new Akeneo\Tool\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            // PimApiBundle must be registered after FOSOAuthServerBundle
            new Akeneo\Tool\Bundle\ApiBundle\PimApiBundle(),
            new Oneup\FlysystemBundle\OneupFlysystemBundle(),
        );
    }

    /**
     * Bundles coming from Symfony Standard Framework.
     *
     * @return array
     */
    protected function getSymfonyBundles()
    {
        return array(
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
        );
    }

    /**
     * * Bundles required by Oro Platform
     *
     * @return array
     */
    protected function getOroDependencies()
    {
        return array(
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
        );
    }

    /**
     * Bundles coming from Oro Platform
     *
     * @return array
     */
    protected function getOroBundles()
    {
        return array(
            new Oro\Bundle\AsseticBundle\OroAsseticBundle(),
            new Oro\Bundle\ConfigBundle\OroConfigBundle(),
            new Oro\Bundle\DataGridBundle\OroDataGridBundle(),
            new Oro\Bundle\FilterBundle\OroFilterBundle(),
            new Oro\Bundle\SecurityBundle\OroSecurityBundle(),
            new Oro\Bundle\TranslationBundle\OroTranslationBundle(),
        );
    }
}
