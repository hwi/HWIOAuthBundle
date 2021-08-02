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
use Twig\Extension\RuntimeExtensionInterface;

final class OAuthRuntime implements RuntimeExtensionInterface
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
     * @return string[]
     */
    public function getResourceOwners(): array
    {
        return $this->helper->getResourceOwners();
    }

    public function getLoginUrl(string $name): string
    {
        return $this->helper->getLoginUrl($name);
    }

    public function getAuthorizationUrl(string $name, ?string $redirectUrl = null, array $extraParameters = []): string
    {
        return $this->helper->getAuthorizationUrl($name, $redirectUrl, $extraParameters);
    }
}
