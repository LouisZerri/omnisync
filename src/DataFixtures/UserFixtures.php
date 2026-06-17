<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private const string ROLE_MANAGER = 'ROLE_MANAGER';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            ['admin@omnisync.test', 'Alice Admin', ['ROLE_ADMIN'], true],
            ['manager@omnisync.test', 'Marc Gestionnaire', [self::ROLE_MANAGER], true],
            ['inactif@omnisync.test', 'Inès Inactive', [self::ROLE_MANAGER], false],
        ];

        foreach ($usersData as [$email, $name, $roles, $isActive]) {
            $user = new User();
            $user->setEmail($email);
            $user->setName($name);
            $user->setRoles($roles);
            $user->setIsActive($isActive);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
