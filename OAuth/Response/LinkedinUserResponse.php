<?php

namespace HWI\Bundle\OAuthBundle\OAuth\Response;


class LinkedinUserResponse extends PathUserResponse
{
    /**
     * Helper to extract the preferred locale value from MultiLocaleString
     * https://docs.microsoft.com/en-us/linkedin/shared/references/v2/object-types#multilocalestring
     *
     * @param $path
     * @return mixed
     */
    protected function getPreferredLocaleValue($path)
    {
        $multiLocaleString = $this->getValueForPath($path);
        $preferredLocale = $multiLocaleString['preferredLocale']['language'] . '_' . $multiLocaleString['preferredLocale']['country'];
        return $multiLocaleString['localized'][$preferredLocale];
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->getPreferredLocaleValue('firstname');
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->getPreferredLocaleValue('lastname');
    }

    /**
     * {@inheritdoc}
     */
    public function getProfilePicture()
    {
        // https://docs.microsoft.com/en-us/linkedin/shared/references/v2/profile/profile-picture
        $profilePicture = $this->getValueForPath('profilepicture');
        if (
            !isset($profilePicture['displayImage~'])
            || !isset($profilePicture['displayImage~']['elements'])
            || count($profilePicture['displayImage~']['elements']) == 0
        ) {
            return null;
        }

        $publicElements = array_filter($profilePicture['displayImage~']['elements'], function($element) {
            return $element['authorizationMethod'] === 'PUBLIC';
        });
        if (count($publicElements) == 0) return null;

        // the last images seems to always be the one with the best quality so we take this one
        $element = array_values(array_slice($publicElements, -1))[0];
        return $element['identifiers'][0]['identifier'];
    }
}
