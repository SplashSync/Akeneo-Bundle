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

namespace Splash\Tests\Tools;

use Exception;
use Splash\Bundle\Services\ConnectorsManager;
use Splash\Core\SplashCore as Splash;
use Splash\Local\Local;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Base PhpUnit Test Class for Splash Modules Tests
 *
 * May be overriden for Using Splash Core Test in Specific Environements
 */
class TestCase extends BaseTestCase
{
    use \Splash\Bundle\Tests\ConnectorAssertTrait;
    use \Splash\Bundle\Tests\ConnectorTestTrait;
    use \Splash\Tests\Tools\Traits\ObjectsAssertionsTrait;
    use \Splash\Tests\Tools\Traits\SuccessfulTestPHP7;

    /**
     * Boot Symfony & Setup First Server Connector For Testing
     *
     * @throws Exception
     *
     * @return void
     */
    protected function setUp()
    {
        //====================================================================//
        // Force Debug Mode
        if (!defined('SPLASH_DEBUG')) {
            define('SPLASH_DEBUG', true);
        }

        //====================================================================//
        // Boot Symfony Kernel
        /** @var ContainerInterface $container */
        $container = static::bootKernel()->getContainer();
        /** @var ConnectorsManager $manager */
        $manager = $container->get("splash.connectors.manager");
        /** @var RouterInterface $router */
        $router = $container->get("router");
        //====================================================================//
        // Boot Local Splash Module
        /** @var Local $local */
        $local = Splash::local();
        $local->boot($manager, $router);

        //====================================================================//
        // Init Local Class with First Server Infos
        //====================================================================//

        //====================================================================//
        // Load Servers Namess
        $servers = $manager->getServersNames();
        if (empty($servers)) {
            throw new Exception("No server Configured for Splash");
        }
        $serverIds = array_keys($servers);
        $local->setServerId((string) array_shift($serverIds));

        //====================================================================//
        // Reboot Splash Core Module
        Splash::reboot();
    }

    //====================================================================//
    // CORE : SYMFONY CONTAINER FROM KERNEL
    //====================================================================//

    /**
     * Safe Gets the current container.
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        $container = static::$kernel->getContainer();
        if (!($container instanceof ContainerInterface)) {
            throw new Exception('Unable to Load Container');
        }

        return $container;
    }
}
