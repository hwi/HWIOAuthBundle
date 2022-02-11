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

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Joseph Bielawski <stloyd@gmail.com>
 * @author SensioLabs <contact@sensiolabs.com>
 */
final class SensioConnectUserResponse extends AbstractUserResponse
{
    /**
     * @var \DOMNode
     */
    protected $data;
    /**
     * @var \DOMXPath|null
     */
    private $xpath;

    /**
     * {@inheritdoc}
     */
    public function getUserIdentifier(): string
    {
        /** @var \DOMAttr $attribute */
        $attribute = $this->data->attributes->getNamedItem('id');
        if (null === $attribute->value) {
            throw new \InvalidArgumentException('User identifier was not found in response.');
        }

        return $attribute->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): ?string
    {
        try {
            return $this->getUserIdentifier();
        } catch (\InvalidArgumentException $e) {
            // @phpstan-ignore-next-line BC compatibility
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNickname()
    {
        $username = null;
        $accounts = $this->xpath->query('./foaf:account/foaf:OnlineAccount', $this->data);
        for ($i = 0; $i < $accounts->length; ++$i) {
            /** @var \DOMNode $account */
            $account = $accounts->item($i);
            if ('SensioLabs Connect' === $this->getNodeValue('./foaf:name', $account)) {
                $username = $this->getNodeValue('foaf:accountName', $account);

                break;
            }
        }

        return $username ?: $this->getNodeValue('./foaf:name', $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRealName(): ?string
    {
        return $this->getNodeValue('./foaf:name', $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(): ?string
    {
        return $this->getNodeValue('./foaf:mbox', $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfilePicture(): ?string
    {
        return $this->getNodeValue('./atom:link[@rel="foaf:depiction"]', $this->data, 'link');
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data): void
    {
        $dom = new \DOMDocument();
        try {
            if (!$dom->loadXML($data)) {
                throw new \ErrorException('Could not transform this xml to a \DOMDocument instance.');
            }
        } catch (\Exception $e) {
            throw new AuthenticationException('Could not retrieve valid user info.');
        }

        $this->xpath = new \DOMXPath($dom);

        $nodes = $this->xpath->evaluate('/api/root');
        $user = $this->xpath->query('./foaf:Person', $nodes->item(0));
        if (1 !== $user->length) {
            throw new AuthenticationException('Could not retrieve user info.');
        }

        /** @var \DOMNode $userElement */
        $userElement = $user->item(0);

        $this->data = $userElement;
    }

    /**
     * @return mixed|null
     */
    private function getNodeValue(string $query, \DOMNode $element, string $nodeType = 'normal')
    {
        $nodeList = $this->xpath->query($query, $element);
        if ($nodeList && $nodeList->length > 0) {
            $node = $nodeList->item(0);
            switch ($nodeType) {
                case 'link':
                    /** @var \DOMAttr $attribute */
                    $attribute = $node->attributes->getNamedItem('href');
                    $nodeValue = $attribute->value;
                    break;
                case 'normal':
                default:
                    $nodeValue = $node->nodeValue;
                    break;
            }

            return $this->sanitizeValue($nodeValue);
        }

        return null;
    }

    /**
     * @return bool|string|null
     */
    private function sanitizeValue(string $value)
    {
        if ('true' === $value) {
            return true;
        }

        if ('false' === $value) {
            return false;
        }

        if (empty($value)) {
            return null;
        }

        return $value;
    }
}
