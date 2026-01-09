<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {}

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setFirstName("Kevin");
        $user->setLastName("Stef");
        $user->setEmail("kevin.stefanelli.pro@gmail.com");
        $user->setPassword($this->hasher->hashPassword($user, "kevin.stefanelli.pro@gmail.com"));
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        $user = new User();
        $user->setFirstName("Kevin");
        $user->setLastName("Stef");
        $user->setEmail("kevin@gmail.com");
        $user->setPassword($this->hasher->hashPassword($user, "kevin@gmail.com"));
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);
        $manager->flush();
    }

}
