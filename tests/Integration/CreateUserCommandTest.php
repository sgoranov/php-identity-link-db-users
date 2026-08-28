<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateUserCommandTest extends KernelTestCase
{
    public function testCreatesUserWithGroupsByName(): void
    {
        $tester = $this->commandTester();

        $status = $tester->execute([
            '--username' => 'new_user',
            '--email' => 'new_user@example.com',
            '--first-name' => 'New',
            '--last-name' => 'User',
            '--password' => 'bd2c5e50-0f94-4586-a4f8-0cf75ce783cc',
            '--group' => [AppFixtures::GROUP_NAME],
        ]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('User "new_user" was created.', $tester->getDisplay());

        $user = static::getContainer()->get(UserRepository::class)
            ->findOneBy(['username' => 'new_user']);

        self::assertNotNull($user);
        self::assertSame([AppFixtures::GROUP_NAME], $user->getGroups()->map(
            static fn ($group): string => $group->getName()
        )->toArray());
        self::assertTrue(password_verify(
            'bd2c5e50-0f94-4586-a4f8-0cf75ce783cc',
            $user->getHashedPassword(),
        ));
    }

    public function testRejectsUnknownGroupWithoutCreatingUser(): void
    {
        $tester = $this->commandTester();

        $status = $tester->execute([
            '--username' => 'new_user',
            '--email' => 'new_user@example.com',
            '--first-name' => 'New',
            '--last-name' => 'User',
            '--password' => 'bd2c5e50-0f94-4586-a4f8-0cf75ce783cc',
            '--group' => ['missing_group'],
        ]);

        self::assertSame(Command::INVALID, $status);
        self::assertStringContainsString('Unknown group(s): missing_group.', $tester->getDisplay());
        self::assertNull(static::getContainer()->get(UserRepository::class)
            ->findOneBy(['username' => 'new_user']));
    }

    public function testRejectsMissingPasswordWithoutCreatingUser(): void
    {
        $tester = $this->commandTester();

        $status = $tester->execute([
            '--username' => 'missing_password_user',
            '--email' => 'missing_password_user@example.com',
            '--first-name' => 'Missing',
            '--last-name' => 'Password',
        ]);

        self::assertSame(Command::INVALID, $status);
        self::assertStringContainsString('The --password option is required.', $tester->getDisplay());
        self::assertNull(static::getContainer()->get(UserRepository::class)
            ->findOneBy(['username' => 'missing_password_user']));
    }

    public function testCreatesUserWithoutGroups(): void
    {
        $tester = $this->commandTester();

        $status = $tester->execute([
            '--username' => 'user_without_groups',
            '--email' => 'user_without_groups@example.com',
            '--first-name' => 'No',
            '--last-name' => 'Groups',
            '--password' => 'd10ef5a4-3510-44a5-80a2-5bba2cfbfa26',
        ]);

        self::assertSame(Command::SUCCESS, $status);

        $user = static::getContainer()->get(UserRepository::class)
            ->findOneBy(['username' => 'user_without_groups']);

        self::assertNotNull($user);
        self::assertCount(0, $user->getGroups());
    }

    public function testCreatesUserWithGrantTypes(): void
    {
        $tester = $this->commandTester();

        $status = $tester->execute([
            '--username' => 'user_with_grant_types',
            '--email' => 'user_with_grant_types@example.com',
            '--first-name' => 'Grant',
            '--last-name' => 'Types',
            '--password' => '0fe5af69-042b-4994-b7ad-c8b043668a06',
            '--grant-type' => ['password', 'refresh_token'],
        ]);

        self::assertSame(Command::SUCCESS, $status);

        $user = static::getContainer()->get(UserRepository::class)
            ->findOneBy(['username' => 'user_with_grant_types']);

        self::assertNotNull($user);
        self::assertSame(['password', 'refresh_token'], $user->getGrantTypes());
    }

    public function testDuplicateGrantTypeArgumentsAreStoredOnlyOnce(): void
    {
        $tester = $this->commandTester();

        $status = $tester->execute([
            '--username' => 'duplicate_grant_types_user',
            '--email' => 'duplicate_grant_types_user@example.com',
            '--first-name' => 'Duplicate',
            '--last-name' => 'GrantTypes',
            '--password' => 'e32118fd-6fe1-47c3-ac65-f186617860cd',
            '--grant-type' => ['authorization_code', 'authorization_code'],
        ]);

        self::assertSame(Command::SUCCESS, $status);

        $user = static::getContainer()->get(UserRepository::class)
            ->findOneBy(['username' => 'duplicate_grant_types_user']);

        self::assertNotNull($user);
        self::assertSame(['authorization_code'], $user->getGrantTypes());
    }

    public function testRejectsInvalidGrantTypeWithoutCreatingUser(): void
    {
        $tester = $this->commandTester();

        $status = $tester->execute([
            '--username' => 'invalid_grant_type_user',
            '--email' => 'invalid_grant_type_user@example.com',
            '--first-name' => 'Invalid',
            '--last-name' => 'GrantType',
            '--password' => '93843176-afb3-41e5-ae1f-d3caf6cb3c70',
            '--grant-type' => ['invalid_grant'],
        ]);

        self::assertSame(Command::INVALID, $status);
        self::assertStringContainsString(
            'Invalid grantTypes: The value "invalid_grant" is not a valid choice.',
            $tester->getDisplay(),
        );
        self::assertNull(static::getContainer()->get(UserRepository::class)
            ->findOneBy(['username' => 'invalid_grant_type_user']));
    }

    public function testDuplicateGroupArgumentsAttachGroupOnlyOnce(): void
    {
        $tester = $this->commandTester();

        $status = $tester->execute([
            '--username' => 'duplicate_groups_user',
            '--email' => 'duplicate_groups_user@example.com',
            '--first-name' => 'Duplicate',
            '--last-name' => 'Groups',
            '--password' => '97e3a6e3-fcce-4595-b1e6-f5cb20ee0980',
            '--group' => [AppFixtures::GROUP_NAME, AppFixtures::GROUP_NAME],
        ]);

        self::assertSame(Command::SUCCESS, $status);

        $user = static::getContainer()->get(UserRepository::class)
            ->findOneBy(['username' => 'duplicate_groups_user']);

        self::assertNotNull($user);
        self::assertCount(1, $user->getGroups());
        self::assertSame(AppFixtures::GROUP_NAME, $user->getGroups()->first()->getName());
    }

    private function commandTester(): CommandTester
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        return new CommandTester($application->find('app:create-user'));
    }
}
