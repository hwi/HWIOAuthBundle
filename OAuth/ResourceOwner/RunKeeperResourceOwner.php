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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * RunKeeperResourceOwner
 *
 * @author Artem Genvald <genvaldartem@gmail.com>
 */
class RunKeeperResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'realname'       => 'name',
        'profilepicture' => 'medium_picture'
    );

    /**
     * Get user resource
     *
     * @param string $accessToken Access token
     *
     * @return array|null
     */
    public function getUserResource($accessToken)
    {
        $response = $this->httpRequest(
            $this->normalizeUrl($this->options['user_resource_url']),
            null,
            array('Authorization: Bearer ' . $accessToken)
        );

        $content = array();

        if (!empty($response) && $response->isOk()) {
            $content = json_decode($response->getContent(), true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                return null;
            }
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://runkeeper.com/apps/authorize',
            'access_token_url'  => 'https://runkeeper.com/apps/token',
            'infos_url'         => 'https://api.runkeeper.com/profile',
            'user_resource_url' => 'https://api.runkeeper.com/user'
        ));
    }
}
