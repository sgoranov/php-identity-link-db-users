<?php
declare(strict_types=1);

namespace App\Tests\Application;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class InternalControllerTest extends WebTestCase
{
    public function testWithInvalidUsername(): void
    {
        $client = static::createClient([], [
            'SSL_CLIENT_S_DN_Email' => 'fake@phpidentitylink.com',
            'SSL_CLIENT_VERIFY' => 'SUCCESS',
            'HTTPS' => true,
        ]);

        $router = $client->getContainer()->get(RouterInterface::class);
        $client->request(
            'GET',
            $router->generate('internal_index')
        );

        $response = $client->getResponse();
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testWithValidUsername(): void
    {
        $client = static::createClient([], [
            'SSL_CLIENT_S_DN_Email' => 'user@phpidentitylink.com',
            'SSL_CLIENT_VERIFY' => 'SUCCESS',
            'HTTPS' => true,
        ]);

        $router = $client->getContainer()->get(RouterInterface::class);
        $client->request(
            'GET',
            $router->generate('internal_index')
        );

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
    }
}