<?php

namespace HWI\Bundle\OAuthBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class OAuthRuntimeExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('hwi_oauth_authorization_url', [OAuthRuntime::class, 'getAuthorizationUrl']),
            new TwigFunction('hwi_oauth_login_url', [OAuthRuntime::class, 'getLoginUrl']),
            new TwigFunction('hwi_oauth_resource_owners', [OAuthRuntime::class, 'getResourceOwners']),
        ];
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'hwi_oauth';
    }
}
