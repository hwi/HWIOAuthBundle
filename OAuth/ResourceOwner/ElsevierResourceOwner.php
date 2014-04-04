<?php

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * ElsevierResourceOwner
 *
 * @author Pierre-Alexandre Nativel <pierre.alexandre.nativel@gmail.com>
 */
class ElsevierResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        if ($this->options['csrf']) {
            if (null === $this->state) {
                $this->state = $this->generateNonce();
            }

            $this->storage->save($this, $this->state, 'csrf_state');
        }

        $parameters = array_merge(array(
            'response_type' => 'code',
            'client_id'     => $this->options['client_id'],
            'elsevier_targetAppName'     => $this->options['client_secret'],
            'redirect_uri'  => $redirectUri,
        ), $extraParameters);

        return $this->normalizeUrl($this->options['authorization_url'], $parameters);
    }

    /**
     * @param string $url
     * @param array  $parameters
     *
     * @return string
     */
    protected function normalizeUrl($url, array $parameters = array())
    {
        $normalizedUrl  = $url;
        if (!empty($parameters)) {
            $normalizedUrl .= (false !== strpos($url, '?') ? '&' : '?').http_build_query($parameters, '', '&');
            $normalizedUrl .= '%s';
        }

        return $normalizedUrl;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver) {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            array(
                'authorization_url' => 'https://acw.elsevier.com/SSOCore/oauth/authCode',
                'access_token_url'  => 'https://acw.elsevier.com/SSOCore/oauth/accessToken',
                'infos_url'         => '',

                'csrf' => false,
            )
        );
    }
}