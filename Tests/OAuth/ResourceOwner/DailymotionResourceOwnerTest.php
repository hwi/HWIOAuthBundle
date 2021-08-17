<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\DailymotionResourceOwner;

final class DailymotionResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = DailymotionResourceOwner::class;
    protected string $userResponse = <<<json
{
    "id": "1",
    "screenname": "bar"
}
json;

    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'screenname',
        'realname' => 'fullname',
    ];

    public function testDisplayPopup(): void
    {
        $resourceOwner = $this->createResourceOwner(['display' => 'popup']);

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&display=popup',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testInvalidDisplayOptionValueThrowsException(): void
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\ExceptionInterface::class);

        $this->createResourceOwner(['display' => 'invalid']);
    }
}
