<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\DependencyInjection;

use HWI\Bundle\OAuthBundle\DependencyInjection\HWIOAuthExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

/**
 * Code bases on FOSUserBundle tests
 */
class HWIOAuthExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationThrowsExceptionUnlessFirewallNameSet()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['firewall_names']);

        $loader->load(array($config), $this->containerBuilder);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationThrowsExceptionUnlessResourceOwnersSet()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['resource_owners']);

        $loader->load(array($config), $this->containerBuilder);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationThrowsExceptionUnlessClientIdSet()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['resource_owners']['any_name']['client_id']);

        $loader->load(array($config), $this->containerBuilder);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationThrowsExceptionUnlessClientSecretSet()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        unset($config['resource_owners']['any_name']['client_secret']);

        $loader->load(array($config), $this->containerBuilder);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationThrowsExceptionWhenPathIsEmpty()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['any_name']['paths'] = array(
            'path' => ''
        );

        $loader->load(array($config), $this->containerBuilder);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationThrowsExceptionWhenUnknownResourceOwnerIsCalled()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['unknown'] = array(
            'type'          => 'unknown',
            'client_id'     => 'client_id',
            'client_secret' => 'client_secret',
        );

        $loader->load(array($config), $this->containerBuilder);
    }

    /**
     * @dataProvider provideInvalidData
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationThrowsExceptionResourceOwnerRequiresSomeOptions($invalidConfig)
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = array(
            $invalidConfig
        );

        $loader->load(array($config), $this->containerBuilder);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationThrowsExceptionWhenServiceHasSomePaths()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['some_service']['paths'] = array(
            'identifier' => 'some_id',
            'nickname'   => 'some_nick',
            'realname'   => 'some_name',
        );

        $loader->load(array($config), $this->containerBuilder);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationThrowsExceptionWhenServiceHasMoreOptions()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners']['some_service']['client_id'] = 'client_id';

        $loader->load(array($config), $this->containerBuilder);
    }

    public function testConfigurationPassValidOAuth1()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = array(
            'valid' => array(
                'type'              => 'oauth1',
                'client_id'         => 'client_id',
                'client_secret'     => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'request_token_url' => 'http://test.pl/request_token_url',
                'access_token_url'  => 'http://test.pl/access_token_url',
                'infos_url'         => 'http://test.pl/infos_url',
                'paths'             => array(
                    'identifier' => 'some_id',
                    'nickname'   => 'some_nick',
                    'realname'   => 'some_name',
                ),
            ),
        );

        $loader->load(array($config), $this->containerBuilder);
    }

    public function testConfigurationPassValidOAuth2WithPaths()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = array(
            'valid' => array(
                'type'              => 'oauth2',
                'client_id'         => 'client_id',
                'client_secret'     => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url'  => 'http://test.pl/access_token_url',
                'infos_url'         => 'http://test.pl/infos_url',
                'paths'             => array(
                    'identifier' => 'some_id',
                    'nickname'   => 'some_nick',
                    'realname'   => 'some_name',
                ),
            ),
        );

        $loader->load(array($config), $this->containerBuilder);
    }

    public function testConfigurationPassValidOAuth2WithDeepPaths()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = array(
            'valid' => array(
                'type'              => 'oauth2',
                'client_id'         => 'client_id',
                'client_secret'     => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url'  => 'http://test.pl/access_token_url',
                'infos_url'         => 'http://test.pl/infos_url',
                'paths'             => array(
                    'identifier' => 'some_id',
                    'nickname'   => 'some_nick',
                    'realname'   => array('first_name', 'last_name'),
                ),
            ),
        );

        $loader->load(array($config), $this->containerBuilder);
    }

    public function testConfigurationPassValidOAuth2WithResponseClass()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $config['resource_owners'] = array(
            'valid' => array(
                'type'                => 'oauth2',
                'client_id'           => 'client_id',
                'client_secret'       => 'client_secret',
                'authorization_url'   => 'http://test.pl/authorization_url',
                'access_token_url'    => 'http://test.pl/access_token_url',
                'infos_url'           => 'http://test.pl/infos_url',
                'user_response_class' => 'SomeClassName',
            ),
        );

        $loader->load(array($config), $this->containerBuilder);
    }

    public function testConfigurationLoadDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter(array('secured_area'), 'hwi_oauth.firewall_names');
        $this->assertParameter(null, 'hwi_oauth.target_path_parameter');
        $this->assertParameter(false, 'hwi_oauth.use_referer');
        $this->assertParameter('hwi_oauth_connect', 'hwi_oauth.failed_auth_path');
        $this->assertParameter(array('any_name' => 'any_name', 'some_service' => 'some_service'), 'hwi_oauth.resource_owners');

        $this->assertNotHasDefinition('hwi_oauth.user.provider.fosub_bridge');

        $this->assertParameter(false, 'hwi_oauth.connect');

        $this->assertParameter('twig', 'hwi_oauth.templating.engine');

        $this->assertAlias('security.user_checker', 'hwi_oauth.user_checker');
    }

    public function provideInvalidData()
    {
        return array(
            'missing_request_token_url' => array(
                'type'              => 'oauth1',
                'client_id'         => 'client_id',
                'client_secret'     => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url'  => 'http://test.pl/access_token_url',
                'infos_url'         => 'http://test.pl/infos_url',
                'paths'             => array(
                    'identifier' => 'some_id',
                    'nickname'   => 'some_nick',
                    'realname'   => 'some_name',
                )
            ),
            'missing_client_secret' => array(
                'type'              => 'oauth1',
                'client_id'         => 'client_id',
            ),
            'missing_client_id' => array(
                'type'              => 'oauth1',
                'client_secret'     => 'client_secret',
            ),
            'missing_paths' => array(
                'type'              => 'oauth2',
                'client_id'         => 'client_id',
                'client_secret'     => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url'  => 'http://test.pl/access_token_url',
                'infos_url'         => 'http://test.pl/infos_url',
            ),
            'missing_some_of_paths' => array(
                'type'              => 'oauth2',
                'client_id'         => 'client_id',
                'client_secret'     => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url'  => 'http://test.pl/access_token_url',
                'infos_url'         => 'http://test.pl/infos_url',
                'paths'             => array(
                    'identifier' => 'some_id',
                    'realname'   => 'some_name',
                )
            ),
            'empty_paths' => array(
                'type'              => 'oauth2',
                'client_id'         => 'client_id',
                'client_secret'     => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url'  => 'http://test.pl/access_token_url',
                'infos_url'         => 'http://test.pl/infos_url',
                'paths'             => array()
            ),
            'path_is_null' => array(
                'type'              => 'oauth2',
                'client_id'         => 'client_id',
                'client_secret'     => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url'  => 'http://test.pl/access_token_url',
                'infos_url'         => 'http://test.pl/infos_url',
                'paths'             => array(
                    'path' => null,
                )
            ),
            'path_is_empty_array' => array(
                'type'              => 'oauth2',
                'client_id'         => 'client_id',
                'client_secret'     => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url'  => 'http://test.pl/access_token_url',
                'infos_url'         => 'http://test.pl/infos_url',
                'paths'             => array(
                    'path' => array(),
                )
            ),
            'path_is_empty_string' => array(
                'type'              => 'oauth2',
                'client_id'         => 'client_id',
                'client_secret'     => 'client_secret',
                'authorization_url' => 'http://test.pl/authorization_url',
                'access_token_url'  => 'http://test.pl/access_token_url',
                'infos_url'         => 'http://test.pl/infos_url',
                'paths'             => array(
                    'path' => '',
                )
            ),
        );
    }

    protected function createEmptyConfiguration()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getEmptyConfig();
        $loader->load(array($config), $this->containerBuilder);
    }

    protected function createFullConfiguration()
    {
        $loader = new HWIOAuthExtension();
        $config = $this->getFullConfig();
        $loader->load(array($config), $this->containerBuilder);
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

    protected function setUp()
    {
        $this->containerBuilder = new ContainerBuilder();
    }

    protected function tearDown()
    {
        $this->containerBuilder = null;
        unset($this->containerBuilder);
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
     * @param mixed $value
     * @param string $key
     */
    private function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->containerBuilder->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    /**
     * @param string $id
     */
    private function assertNotHasDefinition($id)
    {
        $this->assertFalse($this->containerBuilder->hasDefinition($id) || $this->containerBuilder->hasAlias($id));
    }
}
