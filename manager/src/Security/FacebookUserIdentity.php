<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FacebookUserIdentity implements UserInterface, EquatableInterface
{
    private $id;
    private $username;
    private $password;
    private $display;
    private $role;
    private $status;
    private $networks;

    public function __construct(
        string $id,
        string $username,
        string $password,
        string $display,
        string $role,
        string $status,
        string $networks
    )
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->display = $display;
        $this->role = $role;
        $this->status = $status;
        $this->networks = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isActive(): bool
    {
        return $this->status === User::STATUS_ACTIVE;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return [$this->role];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {

    }

    public function getUserIdentifier()
    {
        return $this->getUsername();
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return
            $this->id === $user->id &&
            $this->password === $user->password &&
            $this->role === $user->role &&
            $this->status === $user->status;
    }
    public function getNetworks(): array
    {
        return $this->networks->toArray();
    }
}
