<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use DateTime;
use Symfony\Component\PasswordHasher\Hasher\customerPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CustomerFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i < 4; $i++) {
            $customer= new Customer();
            $customer->setName(sprintf("Customer%d", $i))
                 ->setSiret(sprintf('XXX XXX XXX XXXX%d',$i))
                 ->setIsAllowed(1);
            $manager->persist($customer);
            $this->addReference('Customer'. $i, $customer);
        }

        $manager->flush();
    }
}
