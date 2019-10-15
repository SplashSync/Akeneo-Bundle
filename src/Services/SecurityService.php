<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Akeneo\Services;

use Monolog\Logger;
use Oro\Bundle\UserBundle\Security\UserProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * User Security Features Manager.
 */
class SecurityService
{
    /**
     * @var UserProvider
     */
    private $provider;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Service  Constructor.
     *
     * @param string                   $environment
     * @param Logger                   $logger
     * @param SessionInterface         $session
     * @param TokenStorageInterface    $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(UserProvider $provider, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher)
    {
        $this->provider = $provider;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
    }

    //====================================================================//
    // User Session Management
    //====================================================================//

    /**
     * Check if an User is Currently Logged.
     *
     * @return null|string
     */
    public function getSessionUsername(): ?string
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }

        return $token->getUser()->getUsername();
    }

    /**
     * Ensure Manual Login User.
     *
     * @param string $username
     *
     * @return bool
     */
    public function ensureSessionUser(string $username): bool
    {
        //====================================================================//
        // Ensure User not Already Logged In
        $current = $this->getSessionUsername();
        if ($current) {
            return true;
        }

        $user = $this->provider->loadUserByUsername($username);

        //====================================================================//
        // Generate User Token
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        //====================================================================//
        // Setup Token
        $this->tokenStorage->setToken($token);

//        //====================================================================//
//        // Now dispatch the login event
//        $event = new InteractiveLoginEvent((new Request()), $token);
//        $this->eventDispatcher->dispatch('security.interactive_login', $event);
        
\Splash\Client\Splash::Log()->warTrace("User is Now " . $this->getSessionUsername());

        return true;
    }
}
