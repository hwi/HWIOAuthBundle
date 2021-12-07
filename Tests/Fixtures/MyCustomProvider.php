<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Fixtures;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\State\State;
use HWI\Bundle\OAuthBundle\OAuth\StateInterface;
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;
use Symfony\Component\HttpFoundation\Request;

final class MyCustomProvider implements ResourceOwnerInterface
{
    public function getUserInformation(array $accessToken, array $extraParameters = []): CustomUserResponse
    {
        return new CustomUserResponse();
    }

    public function getAuthorizationUrl($redirectUri, array $extraParameters = []): string
    {
        return '';
    }

    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = []): array
    {
        return [];
    }

    public function isCsrfTokenValid($csrfToken): bool
    {
        return false;
    }

    public function getName(): string
    {
        return 'custom_provider';
    }

    public function getOption($name)
    {
    }

    public function handles(Request $request): bool
    {
        return false;
    }

    public function setName($name): void
    {
    }

    public function addPaths(array $paths): void
    {
    }

    public function refreshAccessToken($refreshToken, array $extraParameters = []): void
    {
    }

    public function getState(): StateInterface
    {
        return new State(null);
    }

    public function addStateParameter(string $key, string $value): void
    {
    }

    public function storeState(StateInterface $state = null): void
    {
    }
}
