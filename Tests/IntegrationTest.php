<?php

declare(strict_types=1);

namespace HWI\Bundle\OAuthBundle\Tests;

use HWI\Bundle\OAuthBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class IntegrationTest extends WebTestCase
{
    public function setUp(): void
    {
        static::$class = AppKernel::class;
    }

    public static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    public function testRequestRedirect(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(302, $response->getStatusCode(), $response->getContent());
        $this->assertSame('http://localhost/login', $response->headers->get('Location'));

        $client->request('GET', $response->headers->get('Location'));

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode(), 'No landing, got redirect to ' . $response->headers->get('Location'));

        $client->clickLink('Login');

        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(302, $response->getStatusCode(), $response->getContent());
        $expectedRedirectUrl = 'https://accounts.google.com/o/oauth2/auth?'
            . http_build_query([
                'response_type' => 'code',
                'client_id' => 'google_client_id',
                'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
                'redirect_uri' => 'http://localhost/login/check-google',
            ]);
        $this->assertSame($expectedRedirectUrl, $response->headers->get('Location'));
    }
}
