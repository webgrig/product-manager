<?php

declare(strict_types=1);

namespace App\Model\User\Service;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use function Symfony\Component\Translation\t;

class PasswordHasher implements PasswordHasherInterface
{
    public function hash(string $password): string
    {
        $hash = password_hash($password, PASSWORD_ARGON2I);
        if ($hash === false) {
            throw new \RuntimeException('Unable to generate hash.');
        }
        return $hash;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return true;
    }
}
