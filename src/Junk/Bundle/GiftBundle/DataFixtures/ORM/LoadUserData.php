<?php
namespace Junk\Bundle\GiftBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Junk\Bundle\GiftBundle\Entity\User;

class LoadUserData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $userAdmin = new User();
        $userAdmin->setUsername('username');
        $userAdmin->setPassword('password');
        $userAdmin->setFirstName('firstname');
        $userAdmin->setLastName('lastname');
        $userAdmin->setEmail('junkbutawesome.os@gmail.com');

        $manager->persist($userAdmin);
        $manager->flush();
    }
}
