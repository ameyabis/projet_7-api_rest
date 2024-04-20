<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Customer;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher){}
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        //customer
        $orange = new Customer();
        $orange->setName('ORANGE');
        $orange->setPhone($faker->phoneNumber());
        $orange->setContact($faker->email());
        $manager->persist($orange);

        $sfr = new Customer();
        $sfr->setName('SFR');
        $sfr->setPhone($faker->phoneNumber());
        $sfr->setContact($faker->email());
        $manager->persist($sfr);

        $bouygues = new Customer();
        $bouygues->setName('BOUYGUES');
        $bouygues->setPhone($faker->phoneNumber());
        $bouygues->setContact($faker->email());
        $manager->persist($bouygues);

        //user
        //ORANGE
        $userOrange1 = new User();
        $userOrange1->setUsername('orange_1');
        $userOrange1->setPassword($this->userPasswordHasher->hashPassword($userOrange1, "password"));
        $userOrange1->setCustomer($orange);
        $userOrange1->setFirstname($faker->firstName());
        $userOrange1->setLastname($faker->lastName());
        $userOrange1->setEmail($faker->email());
        $manager->persist($userOrange1);

        $userOrange2 = new User();
        $userOrange2->setUsername('orange_2');
        $userOrange2->setPassword($this->userPasswordHasher->hashPassword($userOrange2, "password"));
        $userOrange2->setCustomer($orange);
        $userOrange2->setFirstname($faker->firstName());
        $userOrange2->setLastname($faker->lastName());
        $userOrange2->setEmail($faker->email());
        $manager->persist($userOrange2);

        //SFR
        $userSfr1 = new User();
        $userSfr1->setUsername('sfr_1');
        $userSfr1->setPassword($this->userPasswordHasher->hashPassword($userSfr1, "password"));
        $userSfr1->setCustomer($sfr);
        $userSfr1->setFirstname($faker->firstName());
        $userSfr1->setLastname($faker->lastName());
        $userSfr1->setEmail($faker->email());
        $manager->persist($userSfr1);

        $userSfr2 = new User();
        $userSfr2->setUsername('sfr_2');
        $userSfr2->setPassword($this->userPasswordHasher->hashPassword($userSfr2, "password"));
        $userSfr2->setCustomer($sfr);
        $userSfr2->setFirstname($faker->firstName());
        $userSfr2->setLastname($faker->lastName());
        $userSfr2->setEmail($faker->email());
        $manager->persist($userSfr2);

        $userSfr3 = new User();
        $userSfr3->setUsername('sfr_3');
        $userSfr3->setPassword($this->userPasswordHasher->hashPassword($userSfr3, "password"));
        $userSfr3->setCustomer($sfr);
        $userSfr3->setFirstname($faker->firstName());
        $userSfr3->setLastname($faker->lastName());
        $userSfr3->setEmail($faker->email());
        $manager->persist($userSfr3);

        for ($i = 0; $i < 100; $i++) {
            $product = new Product(
                $faker->name(),
                $faker->text(200),
                $faker->randomFloat(2, 0, 1000)
            );

            $manager->persist($product);
        }

        $manager->flush();
    }
}
