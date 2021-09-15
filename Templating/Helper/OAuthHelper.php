<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Templating\Helper;

use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\Helper\Helper;

/**
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 *
 * @final since 1.4
 */
class OAuthHelper extends Helper
{
    /**
     * @var OAuthUtils
     */
    private $oauthUtils;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(OAuthUtils $oauthUtils, RequestStack $requestStack)
    {
        $this->oauthUtils = $oauthUtils;
        $this->requestStack = $requestStack;
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
        return $this->oauthUtils->getLoginUrl($this->getMainRequest(), $name);
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
        return $this->oauthUtils->getAuthorizationUrl($this->getMainRequest(), $name, $redirectUrl, $extraParameters);
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

    /**
     * @return Request|null
     */
    private function getMainRequest()
    {
        if (method_exists($this->requestStack, 'getMainRequest')) {
            return $this->requestStack->getMainRequest(); // Symfony 5.3+
        }

        return $this->requestStack->getMasterRequest();
    }
}
