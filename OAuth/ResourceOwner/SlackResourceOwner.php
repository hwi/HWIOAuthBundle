<?php
namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;

/**
 * Class SlackResourceOwner.
 *
 * @package   Chaplean\Bundle\OAuthBundle\OAuth\ResourceOwner
 * @author    Tom - Chaplean <tom@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     0.1.0
 */
class SlackResourceOwner extends GenericOAuth2ResourceOwner
{
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'name',
        'realname'   => 'profile.real_name',
        'email'      => 'profile.email',
    );

    /**
     * Configure options.
     *
     * @param OptionsResolverInterface $resolver Resolver.
     *
     * @return void
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            array(
                'authorization_url'        => 'https://slack.com/oauth/authorize',
                'access_token_url'         => 'https://slack.com/api/oauth.access',
                'infos_url'                => 'https://slack.com/api/auth.test',
                'user_url'                 => 'https://slack.com/api/users.info',
                'scope'                    => 'identify,read,post',
                'use_commas_in_scope'      => true,
                'use_bearer_authorization' => false
            )
        );

        $resolver->setOptional(array('team'));
    }

    /**
     * Get Authorization Url
     *
     * @param string $redirectUri     Redirect URI
     * @param array  $extraParameters Extra parameters
     *
     * @return string
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        if ($this->options['csrf']) {
            if (null === $this->state) {
                $this->state = $this->generateNonce();
            }

            $this->storage->save($this, $this->state, 'csrf_state');
        }

        $parameters = array_merge(
            array(
                'response_type' => 'code',
                'client_id'     => $this->options['client_id'],
                'scope'         => $this->options['scope'],
                'state'         => $this->state ? urlencode($this->state) : null,
                'redirect_uri'  => $redirectUri,
                'team'          => (isset($this->options['team']) ? $this->options['team'] : '')
            ),
            $extraParameters
        );

        return $this->normalizeUrl($this->options['authorization_url'], $parameters);
    }

    /**
     * Get User Information
     *
     * @param array $accessToken     Access token
     * @param array $extraParameters Extra parameters
     *
     * @return mixed
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        if ($this->options['use_bearer_authorization']) {
            $url = $this->normalizeUrl($this->options['infos_url']);
            $response = $this->httpRequest($url, null, array('Authorization: Bearer ' . $accessToken['access_token']));
        } else {
            $url = $this->normalizeUrl($this->options['infos_url'], array('token' => $accessToken['access_token']));
            $response = $this->doGetUserInformationRequest($url);
        }

        $infosContent = $response->getContent();

        $user = null;

        if ($infosContent != '') {
            $infosJson = json_decode($infosContent, true);

            $url = $this->normalizeUrl(
                $this->options['user_url'],
                array(
                    'token' => $accessToken['access_token'],
                    'user'  => $infosJson['user_id']
                )
            );
            $userResponse = $this->httpRequest($url);

            $userContent = $userResponse->getContent();

            if ($userContent != '') {
                $userJson = json_decode($userContent, true);

                if (isset($userJson['user'])) {
                    $user = array_merge($infosJson, $userJson['user']);
                    $user['token'] = $accessToken['access_token'];
                }
            }
        }

        if ($user === null) {
            throw new AuthenticationException("User data could not be loaded");
        }

        $response = $this->getUserResponse();
        $response->setResponse(json_encode($user));
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }
}
