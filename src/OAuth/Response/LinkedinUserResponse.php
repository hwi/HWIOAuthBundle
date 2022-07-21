<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

final class LinkedinUserResponse extends PathUserResponse
{
    /**
     * {@inheritdoc}
     */
    public function getFirstName(): ?string
    {
        return $this->getPreferredLocaleValue('firstname');
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName(): ?string
    {
        return $this->getPreferredLocaleValue('lastname');
    }

    /**
     * {@inheritdoc}
     */
    public function getProfilePicture(): ?string
    {
        // https://docs.microsoft.com/en-us/linkedin/shared/references/v2/profile/profile-picture
        /** @var array<string, string|array<string, array<int, array<string, mixed>>>> $profilePicture */
        $profilePicture = $this->getValueForPath('profilepicture');
        if (
            !\is_array($profilePicture)
            || !isset($profilePicture['displayImage~']['elements'])
            || 0 === \count($profilePicture['displayImage~']['elements'])
        ) {
            return null;
        }

        $publicElements = array_filter($profilePicture['displayImage~']['elements'], static function ($element) {
            return 'PUBLIC' === $element['authorizationMethod'];
        });
        if (0 === \count($publicElements)) {
            return null;
        }

        // the last images seems to always be the one with the best quality, so we take this one
        $element = array_values(\array_slice($publicElements, -1))[0];

        return $element['identifiers'][0]['identifier'];
    }

    /**
     * Helper to extract the preferred locale value from MultiLocaleString
     * https://docs.microsoft.com/en-us/linkedin/shared/references/v2/object-types#multilocalestring.
     */
    private function getPreferredLocaleValue(string $path): ?string
    {
        /** @var array<string, array<string, string|null>> $multiLocaleString */
        $multiLocaleString = $this->getValueForPath($path);

        $locale = '';
        if (isset($multiLocaleString['preferredLocale'])) {
            $locale = $multiLocaleString['preferredLocale']['language'];
            if (!empty($multiLocaleString['preferredLocale']['country'])) {
                $locale .= '_'.$multiLocaleString['preferredLocale']['country'];
            }
        }

        if (isset($multiLocaleString['localized'][$locale])) {
            return $multiLocaleString['localized'][$locale];
        }

        $fallbackLocale = array_keys($multiLocaleString['localized'])[0];

        return $multiLocaleString['localized'][$fallbackLocale];
    }
}
