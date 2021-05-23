<?php

namespace HWI\Bundle\OAuthBundle\Twig\Extension;

use HWI\Bundle\OAuthBundle\Templating\Helper\OAuthHelper;
use Twig\Extension\RuntimeExtensionInterface;

class OAuthRuntime implements RuntimeExtensionInterface
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
}
