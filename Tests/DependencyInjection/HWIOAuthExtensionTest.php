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

use Http\Client\Common\HttpMethodsClient;
use Http\HttplugBundle\HttplugBundle;
use HWI\Bundle\OAuthBundle\DependencyInjection\HWIOAuthExtension;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\MyCustomProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

/**
 * Code bases on FOSUserBundle tests.
 */
class HWIOAuthExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $containerBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->containerBuilder = new ContainerBuilder();

        $this->containerBuilder->setParameter('kernel.bundles', [
            'HttplugBundle' => new HttplugBundle(),
        ]);
    }

    protected function tearDown(): void
    {
        $this->containerBuilder = null;
        unset($this->containerBuilder);
    }

    public function testHttpClientExists()
    {
        $this->createEmptyConfiguration();

        $this->assertHasDefinition(
            'hwi_oauth.http_client',
            HttpMethodsClient::class
        );
    }

    public function testConfigurationThrowsExceptionUnlessFirewallNameSet()
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['firewall_names']);

        $loader->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionUnlessResourceOwnersSet()
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['resource_owners']);

        $loader->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionUnlessClientIdSet()
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['resource_owners']['any_name']['client_id']);

        $loader->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionUnlessClientSecretSet()
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['resource_owners']['any_name']['client_secret']);

        $loader->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenPathIsEmpty()
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['any_name']['paths'] = [
            'path' => '',
        ];

        $loader->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenUnknownResourceOwnerIsCalled()
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['unknown'] = [
            'type' => 'unknown',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
        ];

        $loader->load([$config], $this->containerBuilder);
    }

    /**
     * @dataProvider provideInvalidData
     *
     * @param array $invalidConfig
     */
    public function testConfigurationThrowsExceptionResourceOwnerRequiresSomeOptions($invalidConfig)
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            $invalidConfig,
        ];

        $loader->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenServiceHasSomePaths()
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['some_service']['paths'] = [
            'identifier' => 'some_id',
            'nickname' => 'some_nick',
            'realname' => 'some_name',
        ];

        $loader->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenServiceHasMoreOptions()
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['some_service']['client_id'] = 'client_id';

        $loader->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenServiceHasClass()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "hwi_oauth.resource_owners.new_resourceowner": You should set at least the \'type\', \'client_id\' and the \'client_secret\' of a resource owner.');

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['new_resourceowner']['class'] = 'My\Class';

        $loader->load([$config], $this->containerBuilder);
    }

    public function testConfigurationThrowsExceptionWhenServiceHasClassAndWrongType()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "hwi_oauth.resource_owners.new_resourceowner": If you\'re setting a \'class\', you must provide a \'oauth1\' or \'oauth2\' type');

        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['new_resourceowner']['class'] = 'My\Class';
        $config['resource_owners']['new_resourceowner']['type'] = 'github';
        $config['resource_owners']['new_resourceowner']['client_id'] = 42;
        $config['resource_owners']['new_resourceowner']['client_secret'] = 'foo';

        $loader->load([$config], $this->containerBuilder);
    }

    public function testConfigurationPassValidOAuth1()
    {
        $loader = new HWIOAuthExtension();
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

        $loader->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationPassValidOAuth2WithPaths()
    {
        $loader = new HWIOAuthExtension();
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

        $loader->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationPassValidOAuth1WithClass()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            'valid' => [
                'type' => 'oauth1',
                'class' => MyCustomProvider::class,
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

        $loader->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationPassValidOAuth2WithClassOnly()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            'valid' => [
                'type' => 'oauth2',
                'class' => MyCustomProvider::class,
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
            ],
        ];

        $loader->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationPassValidOAuth2WithPathsAndClass()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = [
            'valid' => [
                'type' => 'oauth2',
                'class' => MyCustomProvider::class,
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

        $loader->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationPassValidOAuth2WithDeepPaths()
    {
        $loader = new HWIOAuthExtension();
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

        $loader->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationPassValidOAuth2WithResponseClass()
    {
        $loader = new HWIOAuthExtension();
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

        $loader->load([$config], $this->containerBuilder);

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function testConfigurationLoadDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter(['secured_area'], 'hwi_oauth.firewall_names');
        $this->assertParameter(null, 'hwi_oauth.target_path_parameter');
        $this->assertParameter(false, 'hwi_oauth.use_referer');
        $this->assertParameter(false, 'hwi_oauth.failed_use_referer');
        $this->assertParameter('hwi_oauth_connect', 'hwi_oauth.failed_auth_path');
        $this->assertParameter(['any_name' => 'any_name', 'some_service' => 'some_service'], 'hwi_oauth.resource_owners');

        $this->assertNotHasDefinition('hwi_oauth.user.provider.fosub_bridge');

        $this->assertParameter(false, 'hwi_oauth.connect');

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function provideInvalidData()
    {
        return [
            'missing_request_token_url' => [
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
            ],
            'missing_client_secret' => [
                'type' => 'oauth1',
                'client_id' => 'client_id',
            ],
            'missing_client_id' => [
                'type' => 'oauth1',
                'client_secret' => 'client_secret',
            ],
            'missing_paths' => [
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
            ],
            'missing_some_of_paths' => [
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
            ],
            'empty_paths' => [
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [],
            ],
            'path_is_null' => [
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'path' => null,
                ],
            ],
            'path_is_empty_array' => [
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'path' => [],
                ],
            ],
            'path_is_empty_string' => [
                'type' => 'oauth2',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url' => 'http://test.pl/access_token_url',
                'infos_url' => 'http://test.pl/infos_url',
                'paths' => [
                    'path' => '',
                ],
            ],
        ];
    }

    public function testCreateResourceOwnerService()
    {
        $extension = new HWIOAuthExtension();
        $extension->createResourceOwnerService($this->containerBuilder, 'my_github', [
            'type' => 'github',
            'client_id' => '42',
            'client_secret' => 'foo',
        ]);

        $definitions = $this->containerBuilder->getDefinitions();

        $this->assertArrayHasKey('hwi_oauth.resource_owner.my_github', $definitions);
        $this->assertEquals('hwi_oauth.abstract_resource_owner.oauth2', $definitions['hwi_oauth.resource_owner.my_github']->getParent());
        $this->assertEquals('%hwi_oauth.resource_owner.github.class%', $definitions['hwi_oauth.resource_owner.my_github']->getClass());

        $argument2 = $definitions['hwi_oauth.resource_owner.my_github']->getArgument(2);
        $this->assertEquals('42', $argument2['client_id']);
        $this->assertEquals('foo', $argument2['client_secret']);
        $this->assertEquals('my_github', $definitions['hwi_oauth.resource_owner.my_github']->getArgument(3));
    }

    public function testCreateResourceOwnerServiceWithService()
    {
        $extension = new HWIOAuthExtension();
        $extension->createResourceOwnerService($this->containerBuilder, 'external_ressource_owner', [
            'service' => 'my.service',
        ]);

        $aliases = $this->containerBuilder->getAliases();
        $this->assertArrayHasKey('hwi_oauth.resource_owner.external_ressource_owner', $aliases);
        $this->assertEquals('my.service', $aliases['hwi_oauth.resource_owner.external_ressource_owner']);
    }

    public function testCreateResourceOwnerServiceWithWrongClass()
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

    public function testCreateResourceOwnerServiceWithClass()
    {
        $extension = new HWIOAuthExtension();
        $extension->createResourceOwnerService($this->containerBuilder, 'external_ressource_owner', [
            'type' => 'oauth2',
            'class' => MyCustomProvider::class,
            'client_id' => '42',
            'client_secret' => 'foo',
        ]);

        $definitions = $this->containerBuilder->getDefinitions();

        $this->assertArrayHasKey('hwi_oauth.resource_owner.external_ressource_owner', $definitions);
        $this->assertEquals('hwi_oauth.abstract_resource_owner.oauth2', $definitions['hwi_oauth.resource_owner.external_ressource_owner']->getParent());
        $this->assertEquals(MyCustomProvider::class, $definitions['hwi_oauth.resource_owner.external_ressource_owner']->getClass());

        $argument2 = $definitions['hwi_oauth.resource_owner.external_ressource_owner']->getArgument(2);
        $this->assertEquals('42', $argument2['client_id']);
        $this->assertEquals('foo', $argument2['client_secret']);
        $this->assertEquals('external_ressource_owner', $definitions['hwi_oauth.resource_owner.external_ressource_owner']->getArgument(3));
    }

    protected function createEmptyConfiguration()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $loader->load([$config], $this->containerBuilder);
    }

    protected function createFullConfiguration()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getFullConfig();
        $loader->load([$config], $this->containerBuilder);
    }

    /**
     * @return array
     */
    protected function getEmptyConfig()
    {
        $yaml = <<<EOF
firewall_names: [secured_area]
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

    protected function getFullConfig()
    {
        $yaml = <<<EOF
firewall_names: [secured_area]

resource_owners:
    github:
        type:                github
        client_id:           client_id
        client_secret:       client_secret
        scope:               ""

    google:
        type:                google
        client_id:           client_id
        client_secret:       client_secret
        scope:               ""
        user_response_class: \Our\Custom\Response\Class
        paths:
            email:          email
            profilepicture: picture

    facebook:
        type:                facebook
        client_id:           client_id
        client_secret:       client_secret
        scope:               ""
        paths:
            nickname:        [email, id]

    my_custom_oauth2:
        type:                oauth2
        client_id:           client_id
        client_secret:       client_secret
        access_token_url:    https://path.to/oauth/v2/token
        authorization_url:   https://path.to/oauth/v2/authorize
        infos_url:           https://path.to/api/user
        scope:               ""
        user_response_class: HWI\Bundle\OAuthBundle\OAuth\Response\AdvancedPathUserResponse
        paths:
            identifier: id
            nickname:   username
            realname:   username
            email:      email

    my_custom_oauth1:
        type:                oauth1
        client_id:           client_id
        client_secret:       client_secret
        request_token_url:   https://path.to/oauth/v1/requestToken
        access_token_url:    https://path.to/oauth/v1/token
        authorization_url:   https://path.to/oauth/v1/authorize
        infos_url:           https://path.to/api/user
        realm:               ""
        user_response_class: HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse
        paths:
            identifier: id
            nickname:   username
            realname:   username

fosub:
    username_iterations: 30

    properties:
        github: githubId
        google: googleId
        facebook: facebookId
        my_custom_provider: customId

connect:
    registration_form_handler: my_registration_form_handler
    registration_form: my_registration_form
    account_connector: my_link_provider

http_client:
    timeout:       5
    verify_peer:   true
    ignore_errors: true
    max_redirects: 5

templating_engine: "php"
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @param string $value
     * @param string $key
     */
    private function assertAlias($value, $key)
    {
        $this->assertEquals($value, (string) $this->containerBuilder->getAlias($key), sprintf('%s alias is correct', $key));
    }

    /**
     * @param mixed  $value
     * @param string $key
     */
    private function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->containerBuilder->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    /**
     * @param string $id
     * @param string $className
     */
    private function assertHasDefinition($id, $className = null)
    {
        $this->assertTrue(($this->containerBuilder->hasDefinition($id) ?: $this->containerBuilder->hasAlias($id)));

        if (null !== $className) {
            $this->assertSame($this->containerBuilder->findDefinition($id)->getClass(), $className);
        }
    }

    /**
     * @param string $id
     */
    private function assertNotHasDefinition($id)
    {
        $this->assertFalse($this->containerBuilder->hasDefinition($id) || $this->containerBuilder->hasAlias($id));
    }
}
