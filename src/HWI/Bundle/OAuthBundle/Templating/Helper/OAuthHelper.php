<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Templating\Helper;

use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\Helper;

/**
 * OAuthHelper
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class OAuthHelper extends Helper
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var OAuthUtils
     */
    private $oauthUtils;

    /**
     * @param OAuthUtils $oauthUtils
     */
    public function __construct(OAuthUtils $oauthUtils)
    {
        $this->oauthUtils = $oauthUtils;
    }

    /**
     * @param null|Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getResourceOwners()
    {
        return $this->oauthUtils->getResourceOwners();
    }

    /**
     * @param string $name
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getLoginUrl($name)
    {
        return $this->oauthUtils->getLoginUrl($this->request, $name);
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
        return $this->oauthUtils->getAuthorizationUrl($this->request, $name, $redirectUrl, $extraParameters);
    }

    /**
     * Returns the name of the helper.
     *
     * @return string The helper name
     */
    public function getName()
    {
        return 'hwi_oauth';
    }
}
