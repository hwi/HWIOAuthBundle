<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Util;

use HWI\Bundle\OAuthBundle\Util\DomainWhitelist;
use PHPUnit\Framework\TestCase;

class DomainWhitelistTest extends TestCase
{
    /**
     * @dataProvider targetUrlProvider
     *
     * @param string $targetUrl
     * @param array  $domainsWhitelistParameter
     * @param bool   $isValidTargetUrl
     */
    public function testValidateTargetUrl($targetUrl, $domainsWhitelistParameter, $isValidTargetUrl)
    {
        $domainsWhitelist = new DomainWhitelist($domainsWhitelistParameter);
        $this->assertSame($isValidTargetUrl, $domainsWhitelist->isValidTargetUrl($targetUrl));
    }

    public function targetUrlProvider()
    {
        return [
            ['https://example.com/redirect', ['example.com'], true],
            ['https://example.com/redirect', ['foobar.com'], false],
            ['blabla', ['foobar.com'], false],
            ['https://example.com/redirect', ['foobar.com', 'example.com'], true],
        ];
    }
}
