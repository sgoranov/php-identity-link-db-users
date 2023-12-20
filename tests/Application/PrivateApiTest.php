<?php
declare(strict_types=1);

namespace App\Tests\Application;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class PrivateApiTest extends WebTestCase
{
    public function testWithInvalidCredentials(): void
    {
        $client = static::createClient([], [
            'SSL_CLIENT_S_DN_Email' => 'fake@phpidentitylink.com',
            'SSL_CLIENT_VERIFY' => 'SUCCESS',
            'HTTPS' => true,
        ]);

        $router = $client->getContainer()->get(RouterInterface::class);
        $client->request(
            'GET',
            $router->generate('api_private_fetch_user')
        );

        $response = $client->getResponse();
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testBadResponse(): void
    {
        $client = static::createClient([], [
            'SSL_CLIENT_S_DN_Email' => 'user@phpidentitylink.com',
            'SSL_CLIENT_VERIFY' => 'SUCCESS',
            'HTTPS' => true,
        ]);

        $router = $client->getContainer()->get(RouterInterface::class);
        $client->request(
            'GET',
            $router->generate('api_private_fetch_user'),
            ['json' => [
                'username' => 'value1',
                'password' => 'value2',
            ]]
        );

        $response = $client->getResponse();
        var_dump((string)$client->getRequest());exit();

        $this->assertSame(200, $response->getStatusCode());
    }
}