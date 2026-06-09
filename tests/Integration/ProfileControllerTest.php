<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\TestWith;
use sgoranov\IdentityLinkShared\Security\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class ProfileControllerTest extends WebTestCase
{
    public function testUpdateProfileSuccessfullyAsNonAdminUser(): void
    {
        $client = static::createClient();
        $router = $client->getContainer()->get(RouterInterface::class);
        $repository = $client->getContainer()->get(UserRepository::class);
        $updatedUser = $repository->findOneBy(['username' => AppFixtures::USER_USERNAME]);
        $client->loginUser(new User($updatedUser->getId(), ['ROLE_USER']));

        $content = [
            'firstName' => 'ProfileFirstNew',
        ];

        $client->request('PUT', $router->generate('api_v1_update_profile'), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            'ProfileFirstNew',
            json_decode($response->getContent(), true)['response']['user']['firstName']
        );

        $updatedUser = $repository->findOneBy(['username' => AppFixtures::USER_USERNAME]);

        $this->assertSame('ProfileFirstNew', $updatedUser->getFirstName());
    }

    public function testUpdateProfileOnlyUpdatesCurrentUser(): void
    {
        $client = static::createClient();
        $router = $client->getContainer()->get(RouterInterface::class);
        $repository = $client->getContainer()->get(UserRepository::class);
        $currentUser = $repository->findOneBy(['username' => AppFixtures::USER_USERNAME]);
        $client->loginUser(new User($currentUser->getId(), ['ROLE_USER']));

        $content = [
            'firstName' => 'CurrentOnly',
        ];

        $client->request('PUT', $router->generate('api_v1_update_profile'), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());

        $currentUser = $repository->findOneBy(['username' => AppFixtures::USER_USERNAME]);
        $otherUser = $repository->findOneBy(['username' => 'test_user_2']);

        $this->assertSame('CurrentOnly', $currentUser->getFirstName());
        $this->assertSame('First', $otherUser->getFirstName());
    }

    #[TestWith([['username' => 'new_username']])]
    #[TestWith([['groups' => []]])]
    #[TestWith([['grantTypes' => ['client_credentials']]])]
    public function testUpdateProfileRejectsRestrictedFields(array $content): void
    {
        $client = static::createClient();
        $router = $client->getContainer()->get(RouterInterface::class);
        $repository = $client->getContainer()->get(UserRepository::class);

        $currentUser = $repository->findOneBy(['username' => AppFixtures::USER_USERNAME]);
        $client->loginUser(new User($currentUser->getId(), ['ROLE_USER']));

        $client->request('PUT', $router->generate('api_v1_update_profile'), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());

        $currentUser = $repository->findOneBy(['username' => AppFixtures::USER_USERNAME]);
        $this->assertSame(AppFixtures::USER_USERNAME, $currentUser->getUsername());
        $this->assertSame([], $currentUser->getGrantTypes());
        $this->assertCount(1, $currentUser->getGroups());
    }

    public function testUpdateSystemUserIsForbidden()
    {
        $client = static::createClient();
        $repository = $client->getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['username' => AppFixtures::USER_USERNAME]);
        $client->loginUser(new User($user->getId(), ['ROLE_USER']));
        $router = $client->getContainer()->get(RouterInterface::class);

        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        $entityManager->getConnection()->executeStatement(
            'UPDATE "user" SET is_system = true WHERE id = :id',
            ['id' => $user->getId()]
        );
        $entityManager->clear();

        $client->request('PUT', $router->generate('api_v1_update_profile'), [], [], [], json_encode(['firstName' => 'FirstNew']));
        $response = $client->getResponse();

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('System users cannot be updated.', json_decode($response->getContent(), true)['error']);
    }
}
