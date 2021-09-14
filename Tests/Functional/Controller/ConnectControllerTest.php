<?php

declare(strict_types=1);

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Tests\App\AppKernel;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use HWI\Bundle\OAuthBundle\Tests\Functional\AuthenticationHelperTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ConnectControllerTest extends WebTestCase
{
    use AuthenticationHelperTrait;

    protected function setUp(): void
    {
        static::$class = AppKernel::class;
    }

    public static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    public function testRegistration(): void
    {
        $httpClient = new MockHttpClient(
            function ($method, $url, $options) {
                return new MockResponse(
                    '{"access_token":"valid-access-token"}',
                    [
                        'response_headers' => ['content-type' => 'application/json'],
                    ]
                );
            }
        );

        $client = static::createClient();
        $client->disableReboot();
        $client->getContainer()->set('hwi_oauth.http_client', $httpClient);

        $key = 1;
        $exception = new AccountNotLinkedException();
        $exception->setResourceOwnerName('google');
        $exception->setToken(new CustomOAuthToken());

        $session = $this->getSession($client);
        $session->set('_hwi_oauth.registration_error.'.$key, $exception);

        $this->createDatabase($client);

        $crawler = $client->request('GET', '/connect/registration/'.$key);

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame(1, $crawler->filter('.hwi_oauth_registration_register')->count(), $response->getContent());

        $form = $crawler->filter('form')->form();

        $form['registration[email]']->setValue('test@example.com');
        $form['registration[username]']->setValue('foo');
        $form['registration[plainPassword][first]']->setValue('bar');
        $form['registration[plainPassword][second]']->setValue('bar');

        $crawler = $client->submit($form);
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('Successfully registered and connected the account "foo"!', $crawler->filter('h3')->text(), $response->getContent());
    }

    public function testConnectService(): void
    {
        $httpClient = new MockHttpClient(
            function ($method, $url, $options) {
                return new MockResponse(
                    '{"name":"foo"}',
                    [
                        'response_headers' => ['content-type' => 'application/json'],
                    ]
                );
            }
        );

        $client = static::createClient();
        $client->disableReboot();
        $client->getContainer()->set('hwi_oauth.http_client', $httpClient);

        $this->createDatabase($client);

        $session = $this->getSession($client);
        $session->set('_hwi_oauth.connect_confirmation.1', ['access_token' => 'valid-access-token']);

        $this->logIn($client, $session);

        $crawler = $client->request('GET', '/connect/service/google', [
            'key' => '1',
        ]);

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame(1, $crawler->filter('.registration_register')->count(), $response->getContent());

        $form = $crawler->filter('form')->form();

        $crawler = $client->submit($form);
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('Successfully connected the account "foo"!', $crawler->filter('h3')->text(), $response->getContent());
    }

    private function createDatabase(KernelBrowser $client): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metadata);
    }
}
