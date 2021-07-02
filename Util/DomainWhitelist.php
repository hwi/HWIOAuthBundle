<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Util;

/**
 * @final
 */
class DomainWhitelist
{
    /**
     * @var array
     */
    private $targetPathDomainsWhiteList;

    /**
     * @param array<int, string> $targetPathDomainsWhiteList
     */
    public function __construct(array $targetPathDomainsWhiteList)
    {
        $this->targetPathDomainsWhiteList = $targetPathDomainsWhiteList;
    }

    public function isValidTargetUrl(string $targetUrl): bool
    {
        if (0 === \count($this->targetPathDomainsWhiteList)) {
            return true;
        }

        $urlParts = parse_url($targetUrl);
        if (!isset($urlParts['host'])) {
            return false;
        }

        return \in_array($urlParts['host'], $this->targetPathDomainsWhiteList, true);
    }
}
