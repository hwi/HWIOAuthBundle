<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\DependencyInjection;

use HWI\Bundle\OAuthBundle\DependencyInjection\CompilerPass\ResourceOwnerCompilerPass;
use HWI\Bundle\OAuthBundle\DependencyInjection\HWIOAuthExtension;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomResourceOwner;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomResourceOwnerWithoutType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Parser;

/**
 * Code bases on FOSUserBundle tests.
 */
final class HWIOAuthExtensionTest extends TestCase
{
    protected ContainerBuilder $containerBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->containerBuilder = new ContainerBuilder();
    }

    protected function tearDown(): void
    {
        unset($this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenUnknownResourceOwnerIsCalled(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $extension = new HWIOAuthExtension();
        $this->containerBuilder->registerExtension($extension);

        $config = $this->getEmptyConfig();
        $config['resource_owners']['unknown'] = [
            'type' => 'unknown',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
        ];

        $extension->load([$config], $this->containerBuilder);

        $pass = new ResourceOwnerCompilerPass();
        $pass->process($this->containerBuilder);
    }

    #[IgnoreDeprecations]
    #[Group('legacy')]
    public function testConfigurationTriggersDeprecatedNoticeIfFirewallNamesSet(): void
    {
        $extension = new HWIOAuthExtension();
        $this->containerBuilder->registerExtension($extension);

        $config = $this->getEmptyConfig();
        $config['firewall_names'] = ['main'];

        $this->expectNotToPerformAssertions();

        $extension->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionUnlessResourceOwnersSet(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $extension = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['resource_owners']);

        $extension->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionUnlessClientIdSet(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $extension = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['resource_owners']['any_name']['client_id']);

        $extension->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionUnlessClientSecretSet(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $extension = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['resource_owners']['any_name']['client_secret']);

        $extension->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenPathIsEmpty(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $extension = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['any_name']['paths'] = [
            'path' => '',
        ];

        $extension->load([$config], $this->containerBuilder);
    }

    #[IgnoreDeprecations]
    #[Group('legacy')]
    public function testConfigurationThrowsDeprecationWhenTypeAndClassGiven(): void
    {
        $extension = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['unknown'] = [
            'class' => CustomResourceOwner::class,
            'type' => 'unknown',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
        ];

        $this->expectNotToPerformAssertions();

        $extension->load([$config], $this->containerBuilder);
    }

    #[DataProvider('provideInvalidData')]
    public function testConfigurationThrowsExceptionResourceOwnerRequiresSomeOptions(string|array $invalidConfig): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $extension = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            $invalidConfig,
        ];

        $extension->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenServiceHasSomePaths(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $config = $this->getEmptyConfig();
        $config['resource_owners']['some_service']['paths'] = [
            'identifier' => 'some_id',
            'nickname' => 'some_nick',
            'realname' => 'some_name',
        ];

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenServiceHasMoreOptions(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $config = $this->getEmptyConfig();
        $config['resource_owners']['some_service']['client_id'] = 'client_id';

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenServiceHasClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "hwi_oauth.resource_owners.new_resourceowner": You should set at least the \'type\' or \'class\' with \'client_id\' and the \'client_secret\' of a resource owner.');

        $config = $this->getEmptyConfig();
        $config['resource_owners']['new_resourceowner']['class'] = 'My\Class';

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenClassNotImplementingResourceOwnerInterface(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "hwi_oauth.resource_owners.new_resourceowner": Resource owner class should implement "ResourceOwnerInterface", or extended class "GenericOAuth1ResourceOwner"/"GenericOAuth2ResourceOwner".');

        $config = $this->getEmptyConfig();
        $config['resource_owners']['new_resourceowner']['class'] = stdClass::class;
        $config['resource_owners']['new_resourceowner']['client_id'] = 42;
        $config['resource_owners']['new_resourceowner']['client_secret'] = 'foo';

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenTypeConstNotSet(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Resource owner class either should have "TYPE" const defined or end with "ResourceOwner" so that type can be calculated by converting its class name without suffix to "snake_case". Given class name is "HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomResourceOwnerWithoutType"');

        $config = $this->getEmptyConfig();
        $config['resource_owners']['new_resourceowner']['class'] = CustomResourceOwnerWithoutType::class;
        $config['resource_owners']['new_resourceowner']['client_id'] = 42;
        $config['resource_owners']['new_resourceowner']['client_secret'] = 'foo';

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);
    }

    public function testConfigurationPassValidOAuth1(): void
    {
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            'valid' => [
                'type' => 'oauth1',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'request_token_url' => 'http://test.pl/request_token_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'identifier' => 'some_id',
                    'nickname' => 'some_nick',
                    'realname' => 'some_name',
                ],
            ],
        ];

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationPassValidOAuth2WithPaths(): void
    {
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            'valid' => [
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'identifier' => 'some_id',
                    'nickname' => 'some_nick',
                    'realname' => 'some_name',
                ],
            ],
        ];

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    #[IgnoreDeprecations]
    #[Group('legacy')]
    public function testConfigurationPassValidOAuth1WithClass(): void
    {
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            'valid' => [
                'type' => 'oauth1',
                'class' => CustomResourceOwner::class,
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'request_token_url' => 'http://test.pl/request_token_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'identifier' => 'some_id',
                    'nickname' => 'some_nick',
                    'realname' => 'some_name',
                ],
            ],
        ];

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    #[IgnoreDeprecations]
    #[Group('legacy')]
    public function testConfigurationPassValidOAuth2WithClassOnly(): void
    {
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            'valid' => [
                'type' => 'oauth2',
                'class' => CustomResourceOwner::class,
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
            ],
        ];

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    #[IgnoreDeprecations]
    #[Group('legacy')]
    public function testConfigurationPassValidOAuth2WithPathsAndClass(): void
    {
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            'valid' => [
                'type' => 'oauth2',
                'class' => CustomResourceOwner::class,
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'identifier' => 'some_id',
                    'nickname' => 'some_nick',
                    'realname' => 'some_name',
                ],
            ],
        ];

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationPassValidOAuth2WithDeepPaths(): void
    {
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            'valid' => [
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'identifier' => 'some_id',
                    'nickname' => 'some_nick',
                    'realname' => ['first_name', 'last_name'],
                ],
            ],
        ];

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationPassValidOAuth2WithResponseClass(): void
    {
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            'valid' => [
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'user_response_class' => 'SomeClassName',
            ],
        ];

        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationLoadDefaults(): void
    {
        $this->createEmptyConfiguration();

        $this->assertFalse(
            $this->containerBuilder->hasParameter('hwi_oauth.firewall_names'),
            'hwi_oauth.firewall_names is not set'
        );

        $this->assertParameter(null, 'hwi_oauth.target_path_parameter');
        $this->assertParameter(false, 'hwi_oauth.use_referer');
        $this->assertParameter(false, 'hwi_oauth.failed_use_referer');
        $this->assertParameter('hwi_oauth_connect', 'hwi_oauth.failed_auth_path');
        $this->assertParameter(['any_name' => 'any_name', 'some_service' => 'some_service'], 'hwi_oauth.resource_owners');

        $this->assertParameter(false, 'hwi_oauth.connect.confirmation');

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public static function provideInvalidData(): array
    {
        return [
            'missing_request_token_url' => [[
                'type' => 'oauth1',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'identifier' => 'some_id',
                    'nickname' => 'some_nick',
                    'realname' => 'some_name',
                ],
            ]],
            'missing_client_secret' => [[
                'type' => 'oauth1',
                'client_id' => 'client_id',
            ]],
            'missing_client_id' => [[
                'type' => 'oauth1',
                'client_secret' => 'client_secret',
            ]],
            'missing_paths' => [[
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
            ]],
            'missing_some_of_paths' => [[
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'identifier' => 'some_id',
                    'realname' => 'some_name',
                ],
            ]],
            'empty_paths' => [[
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [],
            ]],
            'path_is_null' => [[
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'path' => null,
                ],
            ]],
            'path_is_empty_array' => [[
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'path' => [],
                ],
            ]],
            'path_is_empty_string' => [[
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'path' => '',
                ],
            ]],
        ];
    }

    public function testRegistersResourceOwnerServiceLocator(): void
    {
        $this->createEmptyConfiguration();

        $this->assertTrue($this->containerBuilder->hasAlias('hwi_oauth.resource_owners.locator'));
        $locatorDefinition = $this->containerBuilder->findDefinition('hwi_oauth.resource_owners.locator');

        $this->assertEquals(
            [
                'any_name' => new ServiceClosureArgument(new Reference('hwi_oauth.resource_owner.any_name')),
                'some_service' => new ServiceClosureArgument(new Reference('hwi_oauth.abstract_resource_owner.generic')),
            ],
            $locatorDefinition->getArgument(0)
        );
    }

    public function testCreateResourceOwnerService(): void
    {
        $extension = new HWIOAuthExtension();
        $reference = $extension->createResourceOwnerService($this->containerBuilder, 'my_github', [
            'type' => 'github',
            'client_id' => '42',
            'client_secret' => 'foo',
        ]);

        /** @var array<string, ChildDefinition> $definitions */
        $definitions = $this->containerBuilder->getDefinitions();

        $this->assertSame('hwi_oauth.resource_owner.my_github', (string) $reference);

        $this->assertArrayHasKey('hwi_oauth.resource_owner.my_github', $definitions);
        $this->assertEquals('%hwi_oauth.resource_owner.github.class%', $definitions['hwi_oauth.resource_owner.my_github']->getClass());

        $argument2 = $definitions['hwi_oauth.resource_owner.my_github']->getArgument('$options');
        $this->assertEquals('42', $argument2['client_id']);
        $this->assertEquals('foo', $argument2['client_secret']);
        $this->assertEquals('my_github', $definitions['hwi_oauth.resource_owner.my_github']->getArgument('$name'));
    }

    public function testCreateResourceOwnerServiceWithService(): void
    {
        $extension = new HWIOAuthExtension();
        $reference = $extension->createResourceOwnerService($this->containerBuilder, 'external_ressource_owner', [
            'service' => 'my.service',
        ]);

        $this->assertSame('my.service', (string) $reference);
    }

    public function testCreateResourceOwnerServiceWithWrongClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Class "HWI\\Bundle\\OAuthBundle\\Tests\\DependencyInjection\\MyWrongCustomProvider" must implement interface "HWI\\Bundle\\OAuthBundle\\OAuth\\ResourceOwnerInterface".');

        $extension = new HWIOAuthExtension();
        $extension->createResourceOwnerService($this->containerBuilder, 'external_ressource_owner', [
            'type' => 'oauth2',
            'class' => 'HWI\Bundle\OAuthBundle\Tests\DependencyInjection\MyWrongCustomProvider',
            'client_id' => '42',
            'client_secret' => 'foo',
        ]);
    }

    public function testCreateResourceOwnerServiceWithClass(): void
    {
        $extension = new HWIOAuthExtension();
        $extension->createResourceOwnerService($this->containerBuilder, 'external_ressource_owner', [
            'type' => 'oauth2',
            'class' => CustomResourceOwner::class,
            'client_id' => '42',
            'client_secret' => 'foo',
        ]);

        /** @var array<string, ChildDefinition> $definitions */
        $definitions = $this->containerBuilder->getDefinitions();

        $this->assertArrayHasKey('hwi_oauth.resource_owner.external_ressource_owner', $definitions);
        $this->assertEquals(CustomResourceOwner::class, $definitions['hwi_oauth.resource_owner.external_ressource_owner']->getClass());

        $argument2 = $definitions['hwi_oauth.resource_owner.external_ressource_owner']->getArgument('$options');
        $this->assertEquals('42', $argument2['client_id']);
        $this->assertEquals('foo', $argument2['client_secret']);
        $this->assertEquals('external_ressource_owner', $definitions['hwi_oauth.resource_owner.external_ressource_owner']->getArgument('$name'));
    }

    protected function createEmptyConfiguration(): void
    {
        $config = $this->getEmptyConfig();
        $extension = new HWIOAuthExtension();
        $extension->load([$config], $this->containerBuilder);
    }

    protected function getEmptyConfig(): array
    {
        $yaml = <<<EOF
resource_owners:
    any_name:
        type:                github
        client_id:           client_id
        client_secret:       client_secret
    some_service:
        service:             hwi_oauth.abstract_resource_owner.generic
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    private function assertAlias(string $value, string $key): void
    {
        $this->assertEquals($value, (string) $this->containerBuilder->getAlias($key), \sprintf('%s alias is correct', $key));
    }

    private function assertParameter($value, string $key): void
    {
        $this->assertEquals($value, $this->containerBuilder->getParameter($key), \sprintf('%s parameter is correct', $key));
    }
}
