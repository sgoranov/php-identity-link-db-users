<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class ResetPasswordControllerTest extends WebTestCase
{
    public function testResetPasswordRequestWithInvalidEmail(): void
    {
        $client = static::createClient();
        $router = $client->getContainer()->get(RouterInterface::class);
        $repository = $client->getContainer()->get(UserRepository::class);
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);

        $crawler = $client->request('GET', $router->generate('forgot_password_request'));
        $form = $crawler->filter('form')->form([
            'reset_password_request_form[email]' => 'nonexistent@example.com',
        ]);

        $client->submit($form);

        $response = $client->getResponse();

        $this->assertResponseRedirects();
        $targetUrl = $response->headers->get('Location');
        $expectedUrl = $router->generate('forgot_password_request');
        $this->assertSame($expectedUrl, $targetUrl);

        $client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', 'If your email exists in our system, you will receive a link to reset your password.');

        $entityManager->clear();
        $this->assertEquals(0, $repository->countUsersWithResetPasswordToken());
    }

    public function testResetPasswordRequestWithValidEmail(): void
    {
        $client = static::createClient();
        $router = $client->getContainer()->get(RouterInterface::class);
        $repository = $client->getContainer()->get(UserRepository::class);
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);

        $crawler = $client->request('GET', $router->generate('forgot_password_request'));
        $form = $crawler->filter('form')->form([
            'reset_password_request_form[email]' => AppFixtures::USER_EMAIL,
        ]);

        $client->submit($form);

        $response = $client->getResponse();

        $this->assertResponseRedirects();
        $targetUrl = $response->headers->get('Location');
        $expectedUrl = $router->generate('forgot_password_request');
        $this->assertSame($expectedUrl, $targetUrl);

        $client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', 'If your email exists in our system, you will receive a link to reset your password.');

        $entityManager->clear();
        $this->assertEquals(1, $repository->countUsersWithResetPasswordToken());
    }
}