<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\User;
use App\Service\PasswordHashGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: "test")]
#[When(env: "dev")]
class AppFixtures extends Fixture
{
    const GROUP_NAME = 'test_group';
    const USER_USERNAME = 'test_user';
    const USER_PASSWORD = 'f1080c74-ace7-44e8-8512-d2917d6dcde6';

    public function load(ObjectManager $manager): void
    {
        // Group
        $group = new Group();
        $group->setName(self::GROUP_NAME);
        $manager->persist($group);

        // User
        $user = new User();
        $user->setPassword(PasswordHashGenerator::create(self::USER_PASSWORD));
        $user->setUsername(self::USER_USERNAME);
        $user->setGroups(new ArrayCollection([$group]));
        $user->setEmail('test_email@phpidentitylink.com');
        $user->setFirstName('First');
        $user->setLastName('Last');
        $manager->persist($user);

        $manager->flush();
    }
}
