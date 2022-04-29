<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Customer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private $encoder;
    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 10; $i++) {
            $user= new User();
            $password = $this->encoder->hashPassword($user, 'password');
            $user->setPassword($password);
            $user->setEmail(sprintf("email%d@example.com", $i))
                 ->setFirstname(sprintf('FirstName%d',$i))
                 ->setUserName(sprintf('UserName%d',$i))
                 ->setRoles(['ROLE_USER'])
                 ->setCustomer($this->getReference('Customer'.rand(1,3)))
                 ->setLastname(sprintf('Lastname%d',$i));
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CustomerFixtures::class
        ];
    }
}
