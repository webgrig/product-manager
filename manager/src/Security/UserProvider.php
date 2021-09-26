<?php

declare(strict_types=1);

namespace App\Security;

use App\ReadModel\User\UserFetcher;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private UserFetcher $users;

    public function __construct(
        UserFetcher $users
    )
    {

        $this->users = $users;
    }
    /**
     * The loadUserByIdentifier() method was introduced in Symfony 5.3.
     * In previous versions it was called loadUserByUsername()
     *
     * Symfony calls this method if you use features like switch_user
     * or remember_me. If you're not using these features, you do not
     * need to implement this method.
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByUsername($identifier);
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $identity)
    {
        if (!$identity instanceof UserIdentity) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($identity)));
        }

        return $this->loadUserByUsername($identity->getUsername());

    }


    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class)
    {
        return UserIdentity::class === $class || is_subclass_of($class, UserIdentity::class);
    }


    public function loadUserByUsername(string $username)
    {

        $chunks = explode(':', $username);


        if (\count($chunks) === 2) {
            $user = $this->users->findForAuthByNetwork($chunks[0], $chunks[1]);
        }

        if (!isset($user))
        {
            $user = $this->users->findForAuthByEmail($username);
        }

        if (null === $user)
        {
            throw new UserNotFoundException('Пользователь не найден.');
        }

        return new UserIdentity(
            $user->id,
            $user->email ?: $username,
            $user->password_hash ?: '',
            $user->name ?: $username,
            $user->role,
            $user->status
        );
    }
}
