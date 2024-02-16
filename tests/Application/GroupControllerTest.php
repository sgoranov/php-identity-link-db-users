<?php
declare(strict_types=1);

namespace App\Tests\Application;

use App\DataFixtures\AppFixtures;
use App\Entity\Group;
use App\Repository\GroupRepository;
use App\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class GroupControllerTest extends WebTestCase
{
    public function testCreateGroupWithMissingBody(): void
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $client->request('POST', $router->generate('api_v1_create_group'));
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testCreateGroupWithEmptyName(): void
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'name' => '',
        ];

        $client->request('POST', $router->generate('api_v1_create_group'), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid name. This value should not be blank.',
            json_decode($response->getContent(), true)['error']);
    }

    public function testCreateGroup(): void
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'name' => 'administrator',
        ];

        $client->request('POST', $router->generate('api_v1_create_group'), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('administrator',
            json_decode($response->getContent(), true)['response']['group']['name']);
    }

    public function testUpdateGroupWithInvalidUuid()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'name' => 'test_new',
        ];

        $client->request('PUT', $router->generate('api_v1_update_group', [
            'id' => 'uuid'
        ]), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid uuid passed.',
            json_decode($response->getContent(), true)['error']);
    }

    public function testUpdateGroupWithInvalidName()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'name' => '%$@!',
        ];

        $repository = $client->getContainer()->get(GroupRepository::class);
        list($group) = $repository->findBy(['name' => AppFixtures::GROUP_NAME]);

        $client->request('PUT', $router->generate('api_v1_update_group', [
            'id' => $group->getId()
        ]), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid name. This value is not valid.',
            json_decode($response->getContent(), true)['error']);
    }

    public function testUpdateGroupSuccessfully()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'name' => 'test_new',
        ];

        $repository = $client->getContainer()->get(GroupRepository::class);
        list($group) = $repository->findBy(['name' => AppFixtures::GROUP_NAME]);

        $client->request('PUT', $router->generate('api_v1_update_group', [
            'id' => $group->getId()
        ]), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('test_new',
            json_decode($response->getContent(), true)['response']['group']['name']);
    }

    public function testDeleteGroupWithMissingUuid()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $client->request('DELETE', $router->generate('api_v1_update_group', [
            'id' => 'c9160e4a-3642-46e6-8b2d-b8aa11bf781b'
        ]));
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Not found.',
            json_decode($response->getContent(), true)['error']);
    }

    public function testDeleteGroupSuccessfully()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $repository = $client->getContainer()->get(GroupRepository::class);
        list($group) = $repository->findBy(['name' => AppFixtures::GROUP_NAME]);

        $client->request('DELETE', $router->generate('api_v1_delete_group', [
            'id' => $group->getId()
        ]));
        $response = $client->getResponse();

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testGetAllWithLimitAsString()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $client->request('GET', $router->generate('api_v1_get_all_groups', []), [], [], [], json_encode([
            'limit' => '10',
            'offset' => 0,
        ]));

        $response = $client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('The limit property must be of type int, but string was provided.',
            json_decode($response->getContent(), true)['error']);
    }

    public function testGetAllWithNegativeLimit()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $client->request('GET', $router->generate('api_v1_get_all_groups', []), [], [], [], json_encode([
            'limit' => -5,
            'offset' => 0,
        ]));

        $response = $client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid limit. This value should be either positive or zero.',
            json_decode($response->getContent(), true)['error']);
    }

    public function testGetAllWithNegativeOffset()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $client->request('GET', $router->generate('api_v1_get_all_groups', []), [], [], [], json_encode([
            'limit' => 10,
            'offset' => -1,
        ]));

        $response = $client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid offset. This value should be either positive or zero.',
            json_decode($response->getContent(), true)['error']);
    }

    public function testGetAllSuccessfully()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        for ($i = 1; $i <= 10; $i++) {
            $group = new Group();
            $group->setName('test_group_' . $i);
            $entityManager->persist($group);
        }
        $entityManager->flush();

        $client->request('GET', $router->generate('api_v1_get_all_groups', []), [], [], [], json_encode([
            // default value are:
            // limit => 10
            // offset => 0
        ]));
        $response = $client->getResponse();
        
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(true,
            json_decode($response->getContent(), true)['response']['hasMore']);
        $this->assertSame(10,
            count(json_decode($response->getContent(), true)['response']['result']));
    }
}