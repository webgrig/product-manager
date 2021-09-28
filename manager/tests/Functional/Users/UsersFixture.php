<?php

declare(strict_types=1);

namespace App\Tests\Functional\Users;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\Name;
use App\Tests\Builder\User\UserBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class UsersFixture extends Fixture implements FixtureGroupInterface
{
    public const EXISTING_ID = '00000000-0000-0000-0000-000000000001';

    public static function getGroups(): array
    {
        return ['test'];
    }
    public function load(ObjectManager $manager): void
    {
        $existing = (new UserBuilder())
            ->viaEmail(new Email('exesting-user@app.test'), 'hash')
            ->confirmed()
            ->build();

        $manager->persist($existing);

        $show = (new UserBuilder())
            ->viaEmail(new Email('show-user@app.test'), 'hash')
            ->withName(new Name('Show', 'User'))
            ->withId(new Id(self::EXISTING_ID))
            ->confirmed()
            ->build();

        $manager->persist($show);

        $manager->flush();
    }
}
