<?php
declare(strict_types=1);

namespace App\Tests\Application;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use sgoranov\PHPIdentityLinkShared\Security\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class UserControllerTest extends WebTestCase
{
    public function testCreateUserWithMissingBody(): void
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $client->request('POST', $router->generate('api_v1_create_user'));
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testCreateUserWithEmptyUsername(): void
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'firstName' => 'First',
            'lastName' => 'Last',
            'email' => 'test@phpidentitylink.com',
            'username' => '',
            'password' => 'test',
        ];

        $client->request('POST', $router->generate('api_v1_create_user'), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid username. This value should not be blank.',
            json_decode($response->getContent(), true)['error']);
    }

    public function testCreateUserWithInvalidUsername(): void
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'firstName' => 'First',
            'lastName' => 'Last',
            'email' => 'test@phpidentitylink.com',
            'username' => 'test$',
            'password' => 'test',
        ];

        $client->request('POST', $router->generate('api_v1_create_user'), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid username. This value is not valid.',
            json_decode($response->getContent(), true)['error']);
    }

    public function testCreateUserWithExistingUsername(): void
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'firstName' => 'First',
            'lastName' => 'Last',
            'email' => 'test@phpidentitylink.com',
            'username' => 'test_user',
            'password' => 'test',
        ];

        $client->request('POST', $router->generate('api_v1_create_user'), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid username. The value "test_user" already exists.',
            json_decode($response->getContent(), true)['error']);
    }

    public function testCreateUserSuccessfully(): void
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'firstName' => 'First',
            'lastName' => 'Last',
            'email' => 'test@phpidentitylink.com',
            'username' => 'test',
            'password' => 'test',
        ];

        $client->request('POST', $router->generate('api_v1_create_user'), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('test',
            json_decode($response->getContent(), true)['response']['user']['username']);
    }

    public function testUpdateUserWithInvalidUuid()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'firstName' => 'FirstNew',
        ];

        $client->request('PUT', $router->generate('api_v1_update_user', [
            'id' => 'uuid'
        ]), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testUpdateUserSuccessfully()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $content = [
            'firstName' => 'FirstNew',
        ];

        $repository = $client->getContainer()->get(UserRepository::class);
        list($user) = $repository->findBy(['username' => AppFixtures::USER_USERNAME]);

        $client->request('PUT', $router->generate('api_v1_update_user', [
            'id' => $user->getId()
        ]), [], [], [], json_encode($content));
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('FirstNew',
            json_decode($response->getContent(), true)['response']['user']['firstName']);
    }

    public function testDeleteUserSuccessfully()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $repository = $client->getContainer()->get(UserRepository::class);
        list($user) = $repository->findBy(['username' => AppFixtures::USER_USERNAME]);

        $client->request('DELETE', $router->generate('api_v1_delete_user', [
            'id' => $user->getId()
        ]));
        $response = $client->getResponse();

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testFetchUserSuccessfully()
    {
        $client = static::createClient();
        $testUser = new User('test', ['ROLE_ADMIN']);
        $client->loginUser($testUser);
        $router = $client->getContainer()->get(RouterInterface::class);

        $repository = $client->getContainer()->get(UserRepository::class);
        list($user) = $repository->findBy(['username' => AppFixtures::USER_USERNAME]);

        $client->request('GET', $router->generate('api_v1_fetch_user', [
            'id' => $user->getId()
        ]));
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(AppFixtures::USER_USERNAME,
            json_decode($response->getContent(), true)['response']['user']['username']);
    }
}