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

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Templating\Helper\Helper;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface,
    HWI\Bundle\OAuthBundle\Security\OAuthUtils;

/**
 * OAuthHelper
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class OAuthHelper extends Helper
{
    private $oauthUtils;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(OAuthUtils $oauthUtils)
    {
        $this->oauthUtils = $oauthUtils;
    }

    /**
     * @return array
     */
    public function getResourceOwners()
    {
        return $this->oauthUtils->getResourceOwners();
    }

    /**
     * @param string  $name
     * @param boolean $connect
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getLoginUrl($name, $connect = false)
    {
        return $this->oauthUtils->getLoginUrl($name, $connect);
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
