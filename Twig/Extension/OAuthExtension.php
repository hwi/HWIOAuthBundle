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

use HWI\Bundle\OAuthBundle\Templating\Helper\OAuthHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * OAuthExtension.
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
final class OAuthExtension extends AbstractExtension
{
    /**
     * @var OAuthHelper
     */
    private $helper;

    public function __construct(OAuthHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('hwi_oauth_authorization_url', [$this, 'getAuthorizationUrl']),
            new TwigFunction('hwi_oauth_login_url', [$this, 'getLoginUrl']),
            new TwigFunction('hwi_oauth_resource_owners', [$this, 'getResourceOwners']),
        ];
    }

    /**
     * @return array
     */
    public function getResourceOwners()
    {
        return $this->helper->getResourceOwners();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getLoginUrl($name)
    {
        return $this->helper->getLoginUrl($name);
    }

    /**
     * @param string $name
     * @param string $redirectUrl     Optional
     * @param array  $extraParameters Optional
     *
     * @return string
     */
    public function getAuthorizationUrl($name, $redirectUrl = null, array $extraParameters = [])
    {
        return $this->helper->getAuthorizationUrl($name, $redirectUrl, $extraParameters);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'hwi_oauth';
    }
}
