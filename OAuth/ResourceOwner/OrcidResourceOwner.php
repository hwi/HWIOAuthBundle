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
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
/**
 * OrcidResourceOwner
 *
 */
class OrcidResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'orcid-profile.orcid-identifier.path',
        'nickname'   => 'orcid-profile.orcid-identifier.path',
        'firstname'   => 'orcid-profile.orcid-bio.personal-details.given-names.value',
        'lastname'   => 'orcid-profile.orcid-bio.personal-details.family-name.value',
        'realname'   => 'orcid-profile.orcid-bio.personal-details.family-name.value'
    );
    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        if(!array_key_exists('orcid', $accessToken)) {
            return parent::getUserInformation($accessToken, $extraParameters);
        }
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => 'https://pub.orcid.org/'.$accessToken["orcid"].'/orcid-profile',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json'
                ],
            ]
        );
        $bio = curl_exec($curl);
        $response = $this->getUserResponse();
        $response->setResponse($bio);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));
        return $response;
    }
    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'authorization_url' => 'https://orcid.org/oauth/authorize',
            'access_token_url'  => 'https://pub.orcid.org/oauth/token',
            'infos_url'         => 'http://pub.orcid.org/v1.2',
            'scope'             => '/authenticate',
        ));
    }
}
