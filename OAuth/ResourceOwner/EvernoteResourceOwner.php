<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Buzz\Message\RequestInterface;
use Buzz\Message\MessageInterface;

use Evernote\Client as EvernoteClient;

use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner;

/**
 * Evernote resource owner
 *
 * The Evernote resource SDK MUST be installed to use this resource owner
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class EvernoteResourceOwner extends GenericOAuth1ResourceOwner
{
    /** {@inheritDoc} */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'username',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => null
    );

    /** {@inheritDoc} */
    public function configure()
    {
        if (!class_exists('Evernote\\Client')) {
            throw new \RuntimeException('Install evernote\'s php sdk to use the Evernote resource owner');
        }

        $this->options['request_token_url'] = sprintf($this->options['request_token_url'], $this->options['sandbox'] ? 'sandbox' : 'www');
        $this->options['authorization_url'] = sprintf($this->options['authorization_url'], $this->options['sandbox'] ? 'sandbox' : 'www');
        $this->options['access_token_url'] = sprintf($this->options['access_token_url'], $this->options['sandbox'] ? 'sandbox' : 'www');
    }

    /** {@inheritDoc} */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $client = new EvernoteClient($accessToken['oauth_token'], $this->options['sandbox']);
        $user = $client->getUser();

        $response = $this->getUserResponse();
        $response->setResponse((array) $user);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /** {@inheritDoc} */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'sandbox' => true,
            'infos_url' => null,
            'request_token_url' => 'https://%s.evernote.com/oauth',
            'authorization_url' => 'https://%s.evernote.com/OAuth.action',
            'access_token_url' => 'https://%s.evernote.com/oauth'
        ));
    }
}

