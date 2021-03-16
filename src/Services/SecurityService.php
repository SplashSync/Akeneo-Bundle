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

namespace Splash\Akeneo\Services;

use Akeneo\UserManagement\Bundle\Security\UserProvider as UserProvider;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
     * @param UserProvider             $provider
     * @param TokenStorageInterface    $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        UserProvider $provider,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher
    ) {
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

        /** @var UserInterface $user */
        $user = $token->getUser();

        return $user->getUsername();
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

        return true;
    }
}
