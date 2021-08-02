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

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
final class OAuthExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('hwi_oauth_authorization_url', [OAuthRuntime::class, 'getAuthorizationUrl']),
            new TwigFunction('hwi_oauth_login_url', [OAuthRuntime::class, 'getLoginUrl']),
            new TwigFunction('hwi_oauth_resource_owners', [OAuthRuntime::class, 'getResourceOwners']),
        ];
    }

    public function getName(): string
    {
        return 'hwi_oauth';
    }
}
