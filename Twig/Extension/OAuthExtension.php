<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Twig\Extension;

use HWI\Bundle\OAuthBundle\Templating\Helper\OAuthHelper;

/**
 * OAuthExtension.
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class OAuthExtension extends \Twig_Extension
{
    /**
     * @var OAuthHelper
     */
    protected $helper;

    /**
     * @param OAuthHelper $helper
     */
    public function __construct(OAuthHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('hwi_oauth_authorization_url', array($this, 'getAuthorizationUrl')),
            new \Twig_SimpleFunction('hwi_oauth_login_url', array($this, 'getLoginUrl')),
            new \Twig_SimpleFunction('hwi_oauth_resource_owners', array($this, 'getResourceOwners')),
        );
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
    public function getAuthorizationUrl($name, $redirectUrl = null, array $extraParameters = array())
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
