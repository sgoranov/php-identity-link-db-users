<?php
declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\DataFixtures\AppFixtures;
use App\Entity\GroupScope;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserRepositoryTest extends KernelTestCase
{
    public function testGetScopesReturnsScopesForAudienceFromUsersGroups(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $group = $container->get(GroupRepository::class)
            ->findOneBy(['name' => AppFixtures::GROUP_NAME]);
        $user = $container->get(UserRepository::class)
            ->findOneBy(['username' => AppFixtures::USER_USERNAME]);

        $groupScope = new GroupScope();
        $groupScope->setGroup($group);
        $groupScope->setAudience('https://example.com/orders');
        $groupScope->setScope('orders:read');
        $entityManager->persist($groupScope);
        $entityManager->flush();

        $scopes = $container->get(UserRepository::class)->getScopes(
            $user,
            'https://example.com/orders'
        );

        $this->assertSame(['orders:read'], $scopes);
    }

    public function testGetScopesDoesNotReturnScopesForAnotherAudience(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $user = $container->get(UserRepository::class)
            ->findOneBy(['username' => AppFixtures::USER_USERNAME]);

        $this->assertSame([], $container->get(UserRepository::class)->getScopes(
            $user,
            'https://example.com/unknown'
        ));
    }
}
