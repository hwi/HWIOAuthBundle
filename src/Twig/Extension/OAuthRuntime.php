<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Twig\Extension;

use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
final class OAuthRuntime implements RuntimeExtensionInterface
{
    private OAuthUtils $oauthUtils;
    private RequestStack $requestStack;

    public function __construct(OAuthUtils $oauthUtils, RequestStack $requestStack)
    {
        $this->oauthUtils = $oauthUtils;
        $this->requestStack = $requestStack;
    }

    /**
     * @return string[]
     */
    public function getResourceOwners(): array
    {
        return $this->oauthUtils->getResourceOwners();
    }

    public function getLoginUrl(string $name): string
    {
        return $this->oauthUtils->getLoginUrl($this->getMainRequest(), $name);
    }

    public function getAuthorizationUrl(string $name, string $redirectUrl = null, array $extraParameters = []): string
    {
        return $this->oauthUtils->getAuthorizationUrl($this->getMainRequest(), $name, $redirectUrl, $extraParameters);
    }

    private function getMainRequest(): ?Request
    {
        if (method_exists($this->requestStack, 'getMainRequest')) {
            return $this->requestStack->getMainRequest(); // Symfony 5.3+
        }

        // @phpstan-ignore-next-line
        return $this->requestStack->getMasterRequest();
    }
}
