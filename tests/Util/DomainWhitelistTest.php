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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DomainWhitelistTest extends TestCase
{
    /**
     * @param array<int, string> $domainsWhitelistParameter
     */
    #[DataProvider('targetUrlProvider')]
    public function testValidateTargetUrl(string $targetUrl, array $domainsWhitelistParameter, bool $isValidTargetUrl): void
    {
        $domainsWhitelist = new DomainWhitelist($domainsWhitelistParameter);
        $this->assertSame($isValidTargetUrl, $domainsWhitelist->isValidTargetUrl($targetUrl));
    }

    public static function targetUrlProvider(): array
    {
        return [
            ['https://example.com/redirect', ['example.com'], true],
            ['https://example.com/redirect', ['foobar.com'], false],
            ['blabla', ['foobar.com'], false],
            ['https://example.com/redirect', ['foobar.com', 'example.com'], true],
        ];
    }
}
